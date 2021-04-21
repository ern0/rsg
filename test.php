#!/usr/bin/php
<?

	require("utils.php");
	require("Text.php");
	require("Node.php");
	require("Renderer.php");
	require("Suite.php");


Class RsgSuite extends Suite {

	function test_trivial() {
		
		$this->assert(3==4,"lof");
	}
	
} // class


(new RsgSuite())->main();

?>