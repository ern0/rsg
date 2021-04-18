<?
class Renderer {

	function __construct(&$nodes) {

		$this->nodes = $nodes;
		$this->vars = [];
		$this->result = "";

	} // ctor()


	function render($text) {

		$words = explode(" ", $text);
		foreach ($words as $word) $this->renderWord($word);

	} // render()


	function renderWord($word) {

		if ($this->isReference($word)) {
			$this->renderReference($word);
		} else {
			$this->result .= $word;
		}

	} // renderWord()


	function isReference($word) {

		$firstChar = substr($word,0,1);
		if ($firstChar == '@') return true;
		if ($firstChar == '[') return true;
		if ($firstChar == '#') return true;

		return false;
	} // isReference()


	function renderReference($ref) {

		$this->fullRef = $ref;

		echo("\n" . $this->fullRef . "\n");

		$ref = $this->cutLvalue($ref);
		$ref = $this->cutNodeSelector($ref);
		$ref = $this->cutProp($ref);
		$ref = $this->cutMod($ref);

		echo("  ");
		echo("LVALUE=" . $this->lvalue . " ");
		echo("SEL=" . $this->selector . " ");
		echo("PROP=" . $this->prop . " ");
		echo("MOD=" . $this->mod . " ");

	} // renderReference()


	function cutLvalue($ref) {
		
		$this->lvalue = null;
		if (substr($ref,0,1) != '@') return;

		while (true) {

			$a = explode("[",$ref);
			if (strchr($a[0],"=")) break;

			$a = explode("#",$ref);
			if (strchr($a[0],"=")) break;

			return;
		} // while once

		$pos = strpos($ref,'=');
		$this->lvalue = substr($ref,1,$pos - 1);
		$ref = substr($ref,$pos + 1);
		
		return $ref;
	} // cutLvalue()


	function cutNodeSelector($ref) {

		$firstChar = substr($ref,0,1);

		switch ($firstChar) {

		case '@':
		case '#':
			$pos = strlen($ref);
			foreach (['.','!'] as $char) {
				$charPos = strpos($ref,$char);
				if ($charPos) $pos = min($pos,$charPos);
			}
			$this->selector = substr($ref,0,$pos);
			break;

		case '[':
			$pos = strpos($ref,']') + 1;
			if (!$pos) fatal("incomplete node selector " . $this->fullRef);
			$this->selector = substr($ref,0,$pos);
			break;

		default:
			fatal("invalid node selector: " . $this->fullRef);

		} // switch firstChar

		$ref = substr($ref,$pos);
		return $ref;
	} // cutNodeSelector()


	function cutProp($ref) {

		$firstChar = substr($ref,0,1);
		$pos = strlen($ref);

		if ($firstChar == '.') {
			$charPos = strpos($ref,'!');
			if ($charPos) $pos = min($pos,$charPos);	
			$this->prop = substr($ref,1,$pos - 1);		
		} 
		else {
			$this->prop = "text";
		}

		$ref = substr($ref,$pos);
		return $ref;
	} // cutProp()


	function cutMod($ref) {

		$firstChar = substr($ref,0,1);
		$pos = strlen($ref);

		if ($firstChar == '!') {
			$this->mod = substr($ref,1);
		} else {
			$this->mod = "";
		}

		$ref = substr($ref,$pos);
		return $ref;
	} // cutMod()

} // class
?>