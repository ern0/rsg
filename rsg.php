<?php
	require("engine/utils.php");
	require("engine/Text.php");
	require("engine/Node.php");
	require("engine/Renderer.php");


	function main() {

		if (strlen($_GET["text"])) {
			renderSpecified();
		} else {
			renderDirectory();
		}
	
	} // main()


	function renderSpecified() {

		echoHeader("sans-serif");

		$text = new Text("kontent/" . $_GET["text"]);
		$text->load();

		$renderer = new Renderer(null, $text->nodes);
		$renderer->render("@main");

		echoFooter();

	} // renderSpecified()


	function renderDirectory() {

		echoHeader("monospace");
		echo("select file: <br/>");

		$dir = dir(getcwd() . "/kontent");
		while (($file = $dir->read()) !== false) {
			if (!strstr($file,".txt")) continue;
			echo("&nbsp;<a href=\"");
			echo("rsg.php?text=");
			echo($file);
			echo("\">");
			echo("$file");
			echo("</a><br/>");
		}
		$dir->close();

		echoFooter();

	} // renderDirectory()


	function echoHeader($font) {

		header("Content-Type: text/html; charset=utf-8");	
		
		echo("<html><head><style>\n");
		echo("body { font-family: " . $font . "; font-size: 24px;");
		echo("</style></head><body>\n");

	} // echoHeader()


	function echoFooter() {
		echo("</body></html>\n");
	} // echoFooter()


	main();
?>
