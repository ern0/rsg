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
		echo( $renderer->render("@a=@sel") . "\n");
		echo( $renderer->render("@a=[a.b=c.?].x") . "\n");
		echo( $renderer->render("@a=#sel") . "\n");
		echo( $renderer->render("@a=@sel.x") . "\n");
		echo( $renderer->render("@a=#sel!a") . "\n");
		echo( $renderer->render("@a=@sel.x!a") . "\n");
		echo( $renderer->render("@a=[sel.v].lofasz!u") . "\n");

	} // main()

	main();
?>