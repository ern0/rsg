<?
class Node {

	function __construct($line) {

		$this->props = [];
		$this->procHeader($line);

	} // ctor()


	function procHeader($line) {

		$this->addProp("H", $line);

	} // procHeader()


	function procText($line) {

		$this->addProp("T", $line);
		 
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
				echo("  " . $key . "[" . $index . "] = " . $value . "\n");
			}
		}

	} // dump()

}
?>