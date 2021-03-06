#!/usr/bin/php
<?php

	require("engine/utils.php");
	require("engine/Text.php");
	require("engine/Node.php");
	require("engine/Renderer.php");
	require("Suite.php");

Class RsgSuite extends Suite {


	function parserTest($ref, $lvalue, $sel, $prop, $mod, $message) {

		$renderer = new Renderer(null);
		$renderer->parseReference($ref);

		if ($prop == "") $prop = "text";

		$this->assertEquals($lvalue, $renderer->lvalue, $message . ": lvalue");
		$this->assertEquals($sel, $renderer->selector, $message . ": sel");
		$this->assertEquals($prop, $renderer->prop, $message . ": prop");
		$this->assertEquals($mod, $renderer->mod, $message . ": mod");

	} // parserTest()


	function createRenderer() {

		$this->nodes = [];
		$this->renderer = new Renderer(null, $this->nodes);
		$this->renderer->isDirectRendering = false;
	
	} // createRenderer()


	function createNode($props) {

		$index = sizeof($this->nodes);
		$this->nodes[$index] = new Node($index, "node " . $props);

		$this->node = &$this->nodes[$index];

		return $this->nodes[$index];
	} // createNode()


	function addText($text) {
		$this->node->addProp("text",$text);
	} // addText()


	function assertSelected($positive, $negative = "") {

		$positive = str_replace(",", "][", $positive);
		if (strlen($positive)) $positive = "[" . $positive . "]";
		$pos = $positive;

		$negative = str_replace(",", "][", $negative);
		if (strlen($negative)) $negative = "[" . $negative . "]";
		$neg = $negative;

		foreach ($this->renderer->matchList as $node) {
			$positive = str_replace("[" . $node->props["id"][0] . "]", "", $positive);
			$negative = str_replace("[" . $node->props["id"][0] . "]", "", $negative);
		}

		$message = "selector \"" . $this->renderer->selector . "\"";
		$this->assert($positive == "", $message . ": positive miss: " . $positive);
		$this->assert($negative == $neg, $message . ": negative miss: " , $neg);

	} // assertSelectedAndNot()


	function test_parse_lvalue() {
		$this->parserTest("@main=#search", "main", "tag=search", "", "", "tag");
		$this->parserTest("@main=[id=second]", "main", "id=second", "", "", "expr");
		$this->parserTest("@main=@another", "main", "id=another", "", "", "id");
	}

	function test_parse_selector_id() {
		$this->parserTest("@main", "", "id=main", "", "", "node-only");
		$this->parserTest("@s", "", "id=s", "", "", "node-only-short");
		$this->parserTest("@main.x", "", "id=main", "x", "", "node+prop");
		$this->parserTest("@main.x!u", "", "id=main", "x", "u", "node+prop+mod-s");
		$this->parserTest("@m.x!abc", "", "id=m", "x", "abc", "node+prop+mod-l");
	}

	function test_parse_selector_tag() {
		$this->parserTest("#value", "", "tag=value", "", "", "shortcuts");
		$this->parserTest("#v", "", "tag=v", "", "", "short");
		$this->parserTest("#value.name", "", "tag=value", "name", "", "prop");
		$this->parserTest("#value!xy", "", "tag=value", "", "xy", "mod");
		$this->parserTest("#v!a", "", "tag=v", "", "a", "mod-short");
	}

	function test_parse_selector_expr() {
		$this->parserTest("[color=blue]", "", "color=blue", "", "", "standard");
		$this->parserTest("[c=b]", "", "c=b", "", "", "short");
		$this->parserTest("[color=@a.color]", "", "color=@a.color", "", "", "@-in-expr");
		$this->parserTest("[prop=value]!dudu", "", "prop=value", "", "dudu", "mod");
	}

	function test_parse_selector_prop() {
		$this->parserTest("@node.prop", "", "id=node", "prop", "", "standard");
		$this->parserTest("@node.prop!m", "", "id=node", "prop", "m", "mod-short");
		$this->parserTest("@x=#a.p!m", "x", "tag=a", "p", "m", "all-short");
	}

	function test_parse_selector_mod() {
		$this->parserTest("@a=@x!m", "a", "id=x", "", "m", "shorties-no-prop");
		$this->parserTest("@a=[t=@a.p]!m", "a", "t=@a.p", "", "m", "filter");
		$this->parserTest("@a=[t=@a.p].x!m", "a", "t=@a.p", "x", "m", "filter-full");
		$this->parserTest("@a=#s.x!m", "a", "tag=s", "x", "m", "hash-full");
		$this->parserTest("@a=@n.x!m", "a", "id=n", "x", "m", "id-full");
	}

	function test_parse_endings_simple() {
		$this->parserTest("#search." , "", "tag=search", "", "", "hash-dot");
		$this->parserTest("#search?" , "", "tag=search", "", "", "hash-qmark");
		$this->parserTest("#search!" , "", "tag=search", "", "", "hash-exmark");
		$this->parserTest("#search:" , "", "tag=search", "", "", "hash-col");
	}

	function test_parse_endings_extra() {
		$this->parserTest("#search.t." , "", "tag=search", "t", "", "dot-prop");
		$this->parserTest("@a=[color=red,%w].t!!!" , "a", "color=red,%w", "t", "", "invalid-mod-as-text");
		$this->parserTest("@a=[color=red,%w].t!u!" , "a", "color=red,%w", "t", "u", "excl-after-mod");
	}


	function test_selector_id() {

		$this->createRenderer();
		$this->createNode("id=alpha tag=greek");
		$this->createNode("id=beta tag=greek");
		$this->createNode("id=gamma tag=greek");
		$this->createNode("id=x");

		$this->renderer->parseReference("@alpha");
		$this->renderer->createMatchList();
		$this->assertSelected("alpha");

		$this->renderer->parseReference("@x.p");
		$this->renderer->createMatchList();
		$this->assertSelected("x");

	}


	function test_selector_prop() {

		$this->createRenderer();
		$this->createNode("id=a tag=car size=large color=blue");
		$this->createNode("id=b tag=car size=small color=blue");
		$this->createNode("id=c tag=car size=small color=red");
		$this->createNode("id=d tag=bus size=large color=red");

		$this->renderer->parseReference("#car");
		$this->renderer->createMatchList();
		$this->assertSelected("a,b,c", "d");

		$this->renderer->parseReference("#blue");
		try {
			$this->renderer->createMatchList();
			$this->assert(false, "#blue: no exception, failed");
		} catch (Exception $e) {
			$this->assert(true, "OK");
		}
		$this->assertSelected("", "a,b,c,d");

		$this->renderer->parseReference("[size=large]");
		$this->renderer->createMatchList();
		$this->assertSelected("a,d", "b,c");

		$this->renderer->parseReference("[size=large,color=red]");
		$this->renderer->createMatchList();
		$this->assertSelected("d", "a,b,c");

		$this->renderer->parseReference("[+size=large,-color=red,%w]");
		$this->renderer->createMatchList();
		$this->assertSelected("a", "b,c,d");

	}


	function test_cache_select_simple() {

		$this->createRenderer();

		$this->createNode("id=main");
		$this->addText("@var=#selectme @var @var");

		$this->createNode("tag=selectme");
		$this->addText("#subselect");

		$this->createNode("tag=subselect");
		$this->addText("yep");

		$r = $this->renderer->render("@main");

		$this->assertEquals("yep yep yep", $r, "simple subselect");

	}


	function test_cache_select_shallow() {

		$this->createRenderer();

		$this->createNode("tag=tricolor");
		$this->addText("#color #color #color");

		$this->createNode("tag=color");
		$this->addText("r");
		$this->createNode("tag=color");
		$this->addText("g");
		$this->createNode("tag=color");
		$this->addText("b");

		$this->createNode("id=main");
		$this->addText("@tricolor=#tricolor!h");
		$this->addText("@tricolor");

		$r = $this->renderer->render("@main");
		$this->assert(
			(
				($r == "r g b") || ($r == "g b r") || ($r == "b r g")
				|| 
				($r == "r b g") || ($r == "b g r") || ($r == "g r b")
			)
			,"cached selection issue: \"" . $r . "\""
		);	
	}


	function test_cache_select_deep() {

		$this->createRenderer();

		$this->createNode("tag=color");
		$this->addText("#shade green");
		$this->createNode("tag=shade");
		$this->addText("#how dark");
		$this->createNode("tag=how");
		$this->addText("very");

		$this->createNode("tag=color");
		$this->addText("#shade blue");
		$this->createNode("tag=shade");
		$this->addText("#how dark");
		$this->createNode("tag=how");
		$this->addText("light");

		$this->createNode("id=main");
		$this->addText("@color1=#color!h");
		$this->addText("@color2=#color!h");
		$this->addText("@color1 beats @color2");

		$r = $this->renderer->render("@main");
		$this->assert(
			(
				($r == "very dark blue beats light dark green") 
				|| 
				($r == "very dark green beats light dark blue")
				||
				($r == "light dark blue beats very dark green") 
				|| 
				($r == "light dark green beats very dark blue")
			)
			,"cached selection issue: \"" . $r . "\""
		);	
	}


} // class

(new RsgSuite())->main();
?>
