<?
	require("utils.php");
	require("Text.php");
	require("Node.php");
	require("Renderer.php");


	function main() {

		$text = new Text("test.txt");
		$text->load();

		$renderer = new Renderer(null, $text->nodes);
		echo( $renderer->render("@main") . "\n");

	} // main()

	main();
?>