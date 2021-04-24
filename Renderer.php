<?
class Renderer {

	function __construct($root, &$nodes = null) {

		if ($root == null) {
			$this->root = &$this;
			$this->root->nodes = &$nodes;
		} else {
			$this->root = $root;
		}

		$this->modMute = false;
		$this->modSkipFirstWord = false;
		$this->modCapitalizeFirstLetter = false;

	} // ctor()


	function render($text) {

		$text = str_replace("\t"," ",$text);
		$text = trim($text);
		$words = explode(" ", $text);
		$this->isFirstWord = true;
		foreach ($words as $word) {
			if (!$this->isFirstWord) $this->renderWord(" ");
			$this->renderWord($word);
		}

	} // render()


	function renderWord($word) {

		if ($this->isReference($word)) {
			$remainder = $this->renderReference($word);
			$this->renderWord($remainder);
			return;
		}

		if ($this->modMute) return;

		if ($this->isFirstWord) {

			if ($this->modSkipFirstWord) {
				$this->modSkipFirstWord = false;
				return;
			}

			if ($this->modCapitalizeFirstLetter) {
				$this->modCapitalizeFirstLetter = false;
				$word = $this->capitalize($word);
			}

			$this->isFirstWord = false;
		} // if first word
	
		$this->renderAtom($word);

	} // renderWord()


	function capitalize($word) {

    $firstChar = mb_substr($word, 0, 1, "UTF-8");
    $remaining = mb_substr($word, 1, null, "UTF-8");

    return mb_strtoupper($firstChar, "UTF-8") . $remaining;
	} // capitalize()


	function renderAtom($text) {
		echo($text);
		$this->renderCounter += strlen($text);
	} // renderAtom()


	function isReference($word) {

		$firstChar = substr($word,0,1);
		if ($firstChar == '@') return true;
		if ($firstChar == '[') return true;
		if ($firstChar == '#') return true;

		return false;
	} // isReference()


	function renderReference($ref) {

		$this->fullRef = $ref;
		$ref = $this->parseReference($ref);

		$this->selectNode();

		$renderer = new Renderer($this->root);
		if (strchr($this->mod,'m')) $renderer->modMute = true;
		if (strchr($this->mod,'s')) $renderer->modSkipFirstWord = true;
		if (strchr($this->mod,'c')) $renderer->modCapitalizeFirstLetter = true;

		if (array_key_exists($this->prop,$this->node->props)) {
			$propList = $this->node->props[$this->prop];
		} else {
			$propList = [];
		}

		$first = true;
		$renderer->renderCounter = 0;
		foreach ($propList as $prop) {
			if (!$first) $renderer->renderWord(" ");
			$renderer->render($prop);
			if ($renderer->renderCounter > 0) $first = false;
		}

		return $ref;
	} // renderReference()


	function parseReference($ref) {

		$ref = $this->cutLvalue($ref);
		$ref = $this->cutNodeSelector($ref);
		$ref = $this->cutProp($ref);
		$ref = $this->cutMod($ref);

		return $ref;
	} // parseReference()


	function findWordEnd($ref) {

		$pos = strlen($ref);
		$endings = ".!?:;-(){}$/";
		for ($i = 0; $i < strlen($endings); $i++) {
			$char = substr($endings,$i,1);
			$charPos = strpos($ref,$char);
			if ($charPos) $pos = min($pos,$charPos);
		}
		
		return $pos;
	} // findWordEnd()


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
			$pos = $this->findWordEnd($ref);
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
		$pos = 0;
		$this->prop = "text";
		if (strlen($ref) != 1) {
			$firstChar = substr($ref,0,1);
			if ($firstChar == '.') {
				$pos = $this->findWordEnd(substr($ref,1)) + 1;
				$this->prop = substr($ref,1,$pos - 1);		
			} 
		}

		$ref = substr($ref,$pos);
		return $ref;
	} // cutProp()


	function cutMod($ref) {
		$this->mod = "";

		if (strlen($ref) == 1) return $ref;

		$firstChar = substr($ref,0,1);
		$pos = strlen($ref);

		if ($firstChar == '!') {
			if (ctype_alpha(substr($ref,1))) {
				$this->mod = substr($ref,1);
			}
		}

		$ref = substr($ref,$pos);
		return $ref;
	} // cutMod()


	function createMatchList() {

		$this->createMatchListFilters();
		$this->createMatchListResult();

	} // createMatchList()


	function createMatchListFilters() {

		$this->filters = [];
		$this->weightProp = "weight";
		$selectorItems = explode(",", $this->selector);
	
		foreach ($selectorItems as $selectorItem) {

			$a = explode("=",$selectorItem);
		
			if (substr($a[0],0,1) == '%') {
				$this->weightProp = substr($a[0],1);
			} 
			else {
				$this->filters[$a[0]] = $a[1];
			}

		} // foreach selector item

	} // crteateMatchListFilters()


	function createMatchListResult() {

		$this->matchList = [];
		foreach ($this->root->nodes as $node) {
	
			$match = false;
			foreach ($this->filters as $filterKey => $filterValue) {

				$firstChar = substr($filterKey,0,1);				
				$matchType = true;
				if (($firstChar == '+') || ($firstChar == '-')) {
					$filterKey = substr($filterKey,1);
					if ($firstChar == '-') $matchType = false;
				}

				if (!array_key_exists($filterKey,$node->props)) continue;
				
				foreach ($node->props[$filterKey] as $propIndex => $propValue) {
					if ($propValue == $filterValue) $match = $matchType;
				} // foreach node prop item

			} // foreach filter

			if ($match) $this->matchList[] = $node;

		} // foreach node

		if (sizeof($this->matchList) == 0) {
			throw new Exception("selector does not match: \"" . $this->fullRef . "\"");
		}

	} // createMatchListResult()


	function selectNode() {

		$this->selectNodeFromCache();
		if ($this->node != null) return;

		$this->selectNodeFromDb();
		$this->setLvalue();

	} // selectNode()


	function selectNodeFromCache() {
		
		$this->node == null;
		if (substr($this->selector,0,1) != '@') return;
		$name = substr($this->selector,1);
		if (!array_key_exists($name,$this->root->vars)) return;

		return $this->root->vars[$name];
	} // selectNodeFromCache()


	function selectNodeFromDb() {

		try {
			$this->createMatchList();
		} catch (Exception $e) {
			$this->renderAtom("ERROR(" . $this->selector . ")");
			return;
		}

		foreach ($this->matchList as $node) break;
		$this->node = $node;///

	} // selectNodeFromDb()


	function setLvalue() {

		if ($this->lvalue == "") return;
		$this->root->vars[$this->lvalue] = &$this->node;

	} // setLvalue()

} // class
?>