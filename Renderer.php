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
		$this->parseReference($ref);

	} // renderReference()


	function parseReference($ref) {

//echo("RL:" . $ref . "\n");
		$ref = $this->cutLvalue($ref);
//echo("RS:" . $ref . "\n");
		$ref = $this->cutNodeSelector($ref);
//echo("RP:" . $ref . "\n");
		$ref = $this->cutProp($ref);
//echo("RM:" . $ref . "\n");
		$ref = $this->cutMod($ref);
//echo("RZ:" . $ref . "\n");

		if (false) {
			echo("LVALUE=\"" . $this->lvalue . "\" \n");
			echo("SEL=\"" . $this->selector . "\" \n");
			echo("PROP=\"" . $this->prop . "\" \n");
			echo("MOD=\"" . $this->mod . "\" \n");
			echo("--\n");
		}

	} // parseReference()


	function cutLvalue($ref) {
		
		$this->lvalue = null;
		if (substr($ref,0,1) != '@') return $ref;

		while (true) {

			$a = explode("[",$ref);
			if (strchr($a[0],"=")) break;

			$a = explode("#",$ref);
			if (strchr($a[0],"=")) break;

			return $ref;
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
			if ($firstChar == '@') {
				$this->selector = "id=" . substr($ref,1,$pos - 1);
			}		
			if ($firstChar == '#') {
				$this->selector = "tag=" . substr($ref,1,$pos - 1);
			}
			break;

		case '[':
			$pos = strpos($ref,']') + 1;
			if (!$pos) throw new Exception("incomplete node selector \"" . $this->fullRef . "\"");
			$this->selector = substr($ref,1,$pos - 2);
			break;

		default:
			throw new Exception("invalid node selector: \"" . $this->fullRef . "\"");

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