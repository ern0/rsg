<?
class Renderer {

	function __construct(&$nodes) {
		$this->nodes = $nodes;
	} // ctor()


	function render($text) {

		$this->result = "";

		$words = explode(" ", $text);
		foreach ($words as $word) $this->renderWord($word);

		return $this->result;
	} // render()


	function renderWord($word) {

		$this->result .= $word;

	} // renderWord()

} // class
?>