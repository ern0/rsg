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
		$renderer->isDirectRendering = false;
		$result = $renderer->render("@main");
		if ($_GET["plain"]) {
			$result = str_replace("  ", " ", $result);
		}
		echo($result);

		echoFooter();

	} // renderSpecified()


	function renderDirectory() {

		echoHeader("monospace");
		echo("select file: <br/>");

		$a = [];
		$dir = dir(getcwd() . "/kontent");
		while (($file = $dir->read()) !== false) {
			if (!strstr($file,".txt")) continue;

			$a[] = (
				"&nbsp;&nbsp;<a href=\""
				. "rsg.php?text=" 
				. $file
				. "\">"
				. "$file"
				. "</a>"
				. "&nbsp;-&nbsp;"
				. "<a href=\""
				. "kontent/" . $file
				. "\">"
				. "(source)"
				. "</a>"
				. "<br/>"
			);

		}
		$dir->close();

		sort($a);
		foreach ($a as $x) echo($x);

		echoFooter();

	} // renderDirectory()


	function echoHeader($font) {

		if ($_GET["plain"]) return;

		header("Content-Type: text/html; charset=utf-8");	
		
		echo("<html><head><style>\n");
		echo("body { font-family: " . $font . "; font-size: 24px;");
		echo("</style></head><body>\n");

	} // echoHeader()


	function echoFooter() {

		if ($_GET["plain"]) {
			echo("\n");
		} else {
			echo("</body></html>\n");
		}
	
	} // echoFooter()


	main();
?>
