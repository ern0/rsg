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


	function test_parse_lvalue() {
		$this->parserTest("@main=#search", "main", "tag=search", "", "", "tag");
		$this->parserTest("@main=[id=second]", "main", "id=second", "", "", "expr");
		$this->parserTest("@main=@another", "main", "id=another", "", "", "id");
	}

	function atest_parse_selector_id() {
		$this->parserTest("@main", "", "id=main", "", "", "node-only");
		$this->parserTest("@s", "", "id=s", "", "", "node-only-short");
		$this->parserTest("@main.x", "", "id=main", "x", "", "node+prop");
		$this->parserTest("@main.x!u", "", "id=main", "x", "u", "node+prop+mod-s");
		$this->parserTest("@m.x!abc", "", "id=m", "x", "abc", "node+prop+mod-l");
	}

	function atest_parse_selector_tag() {
		$this->parserTest("#value", "", "tag=value", "", "", "shortcuts");
		$this->parserTest("#v", "", "tag=v", "", "", "short");
	}

	
} // class

(new RsgSuite())->main();
?>