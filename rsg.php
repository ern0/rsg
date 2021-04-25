<?
	require("engine/utils.php");
	require("engine/Text.php");
	require("engine/Node.php");
	require("engine/Renderer.php");


	function main() {

		$text = new Text($_GET["text"]);
		$text->load();

		$renderer = new Renderer(null, $text->nodes);
		$renderer->modCapitalizeFirstLetter = true;
		$renderer->modSkipFirstWord = true;
		$renderer->render("@main");
		echo("\n");

	} // main()

	main();
?>