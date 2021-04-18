<?php
	require("Text.php");
	require("Node.php");
	require("Renderer.php");


	function fatal($text) {

		echo("FATAL: " . $text . "\n");
		die();
	
	} // fatal()


	function main() {

		$text = new Text("test.txt");
		$text->load();

		$renderer = new Renderer($text->nodes);
		$r = $renderer->render("@main");
		echo($r . "\n");

	} // main()

	main();
?>