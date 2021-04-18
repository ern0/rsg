<?php
	require("Text.php");
	require("Node.php");


	function fatal($text) {

		echo("FATAL: " . $text . "\n");
		die();
	
	} // fatal()


	function main() {

		$text = new Text("test.txt");
		$text->load();
		$text->dump();

	} // main()

	main();
?>