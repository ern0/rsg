<?php
class Node {

	function __construct($line) {

		$this->props = [];
		$this->procHeader($line);
		$this->lastTextKey = "text";
		$this->selected = false;

	} // ctor()


	function procHeader($line) {

		$items = explode(" ",$line);
		foreach ($items as $item) {
			
			if ($item == "" || $item == "node") continue;
			
			$a = explode("=", $item);
			$key = $a[0];
			$value = $a[1];

			$this->addProp($key, $value);

		} // foreach item
		
	} // procHeader()


	function procText($line) {

		$a = explode(" ", $line);
		$tag = trim($a[0]);
		if (substr($tag, -1) == "=") {
			$this->lastTextKey = substr($tag, 0, -1);
			$line = trim(substr($line, strlen($tag) + 1));
		}

		$this->addProp($this->lastTextKey, $line);
		 
	} // procText()


	function addProp($key, $value) {
		$this->props[$key][] = $value;
	} // addProp()


	function dump() {

		echo("----\n");
		foreach ($this->props as $key => $values) {
			foreach ($values as $index => $value) {
				$value = trim($value);
				$value = str_replace("\r", "	", $value);
				$value = str_replace("\n", " ", $value);
				echo("  " . $key);
				if (sizeof($values) > 1) echo("[" . $index . "]");
				echo(" = " . $value . "\n");
			}
		}

	} // dump()

}
?>
