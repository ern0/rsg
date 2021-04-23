#!/usr/bin/php
<?

	require("utils.php");
	require("Text.php");
	require("Node.php");
	require("Renderer.php");
	require("Suite.php");

Class RsgSuite extends Suite {


	function parserTest($ref, $lvalue, $sel, $prop, $mod, $message) {

		$renderer = new Renderer($nodes = []);
		$renderer->parseReference($ref);

		if ($prop == "") $prop = "text";

		$this->assertEquals($lvalue, $renderer->lvalue, $message . ": lvalue");
		$this->assertEquals($sel, $renderer->selector, $message . ": sel");
		$this->assertEquals($prop, $renderer->prop, $message . ": prop");
		$this->assertEquals($mod, $renderer->mod, $message . ": mod");

	} // parserTest()


	function createRenderer() {

		$this->nodes = [];
		$this->renderer = new Renderer($this->nodes);
	
	} // createRenderer()


	function createNode($props) {

		$index = sizeof($this->nodes);
		$this->nodes[$index] = new Node("node " . $props);

		return $this->nodes[$index];
	} // createNode()


	function assertSelected($positive, $negative = "") {

		$positive = str_replace(",", "][", $positive);
		$positive = "[" . $positive . "]";
		$negative = str_replace(",", "][", $negative);
		$negative = "[" . $negative . "]";

		$neg = $negative;
		foreach ($this->renderer->matchList as $node) {
			$positive = str_replace("[" . $node->props["id"][0] . "]", "", $positive);
			$negative = str_replace("[" . $node->props["id"][0] . "]", "", $negative);
		}

		$message = "selector \"" . $this->renderer->selector . "\"";
		$this->assert($positive == "", $message . ": positive miss: " . $positive);
		$this->assert($negative == $neg, $message . ": negative miss: " , $negative);

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


	function atest_selector_prop() {

		$this->createRenderer();
		$this->createNode("id=a tag=car size=large color=blue");
		$this->createNode("id=b tag=car size=small color=blue");
		$this->createNode("id=c tag=car size=small color=red");
		$this->createNode("id=d tag=bus size=large color=red");

		$this->renderer->parseReference("#car");
		$this->renderer->createMatchList();
		$this->assertSelected("a,b,c", "d");

		$this->renderer->parseReference("#blue");
		$this->renderer->createMatchList();
		$this->assertSelected("", "a,b,c,d");

		$this->renderer->parseReference("[size=large]");
		$this->renderer->createMatchList();
		$this->assertSelected("a,d", "b,c");

		$this->renderer->parseReference("[size=large,color=red]");
		$this->renderer->createMatchList();
		$this->assertSelected("a,d,c", "b");

		$this->renderer->parseReference("[+size=large,-color=red,%w]");
		$this->renderer->createMatchList();
		$this->assertSelected("a", "b,c,d");

	}

} // class

(new RsgSuite())->main();
?>