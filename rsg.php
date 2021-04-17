<?php
	require("Text.php");

	function fatal($text) {

		echo("FATAL: " . $text);
		die();
	
	} // fatal()


	function main() {

		$text = new Text("test.txt");
		$text->load();
		$text->dump();

	} // main()

	main();
?>