#!/usr/bin/php
<?

	require("utils.php");
	require("Text.php");
	require("Node.php");
	require("Renderer.php");
	require("Suite.php");

Class RsgSuite extends Suite {


	function parserTest($ref, $lvalue, $sel, $prop, $mod) {

		$renderer = new Renderer($nodes = []);
		$renderer->parseReference($ref);

		if ($prop == "") $prop = "text";

		$this->assertEquals($lvalue, $renderer->lvalue, "lvalue");
		$this->assertEquals($sel, $renderer->selector, "sel");
		$this->assertEquals($prop, $renderer->prop, "prop");
		$this->assertEquals($mod, $renderer->mod, "mod");

	} // parserTest()


	function test_parse_lvalue() {
		$this->parserTest("@main", "", "@main", "", "");
	}
	
} // class

(new RsgSuite())->main();
?>