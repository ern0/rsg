<?php
class Renderer {

	function __construct($root, &$nodes = null) {

		if ($root == null) {
			$this->root = &$this;
			$this->root->nodes = &$nodes;
			$this->root->vars = [];
			$this->isDirectRendering = true;
			$this->root->result = "";
		} else {
			$this->root = $root;
		}

		$this->root->modHide = false;
		$this->root->modSkipFirstWord = false;
		$this->root->modCapitalizeFirstLetter = false;

		$this->root->space = " ";
		$this->root->isAtomRendering = false;

	} // ctor()


	function render($text) {

		$text = str_replace("\t"," ",$text);
		$text = trim($text);
		$words = explode(" ", $text);

		$this->renderCounter = 0;
		foreach ($words as $word) {
			$this->renderAtom($this->root->space);
			$this->renderWord($word);
		}

		return $this->root->result;
	} // render()


	function renderWord($word) {

		if ($this->isReference($word)) {
			$remainder = $this->renderReference($word);
			if (strlen($remainder)) {
				$this->root->space = "";
				$this->renderAtom($remainder);
				$this->root->space = " ";
			}
			return;
		}

		$braceted = substr($word,1);
		if ($this->isReference($braceted)) {
			$this->renderWord(substr($word,0,1));
			$this->renderWord($braceted);
			return;
		}

		if ($this->modHide) return;

		if ($this->renderCounter == 0) {

			if ($this->modSkipFirstWord) {
				$this->modSkipFirstWord = false;
				return;
			}

			if ($this->modCapitalizeFirstLetter) {
				$this->modCapitalizeFirstLetter = false;
				$word = $this->capitalize($word);
			}

		} // if first word
	
		$this->renderAtom($word);

	} // renderWord()


	function capitalize($word) {

    $firstChar = mb_substr($word, 0, 1, "UTF-8");
    $remaining = mb_substr($word, 1, null, "UTF-8");

    return mb_strtoupper($firstChar, "UTF-8") . $remaining;
	} // capitalize()


	function renderAtom($text) {

		if (strlen(trim($text))) $this->root->isAtomRendering = true;
		if (!$this->root->isAtomRendering) return;
		
		if ($this->root->isDirectRendering) {
			echo($text);
		} else {
			$this->root->result .= $text;
		}

		$this->renderCounter += strlen(trim($text));

	} // renderAtom()


	function isReference($word) {

		if (strlen($word) < 3) return false;

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
		if ($this->modHide) $renderer->modHide = true;
		if (strchr($this->mod,'h')) $renderer->modHide = true;
		if ($this->modSkipFirstWord) $renderer->modSkipFirstWord = true;
		if (strchr($this->mod,'s')) $renderer->modSkipFirstWord = true;
		if ($this->modCapitalizeFirstLetter) $renderer->modCapitalizeFirstLetter = true;
		if (strchr($this->mod,'c')) $renderer->modCapitalizeFirstLetter = true;

		if (
				($this->node->props != null) 
				&& 
				(array_key_exists($this->prop,$this->node->props))
		) {
			$propList = $this->node->props[$this->prop];
		} else {
			$propList = [];
		}

		$prop = join("", $propList);
		$renderer->render($prop);

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
		$endings = ".!?:,;-(){}$/";
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
		$this->selectorType = ( $firstChar == '@' ? '@' : '#' );
	
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

		if ( ($firstChar == '!') && (ctype_alpha(substr($ref,1,1))) ) {
			$pos = $this->findWordEnd(substr($ref,1)) + 1;
			$this->mod = substr($ref,1,$pos - 1);
		}

		$ref = substr($ref,$pos);
		return $ref;
	} // cutMod()


	function setLvalue() {

		if ($this->lvalue == "") return;
		if (array_key_exists($this->lvalue,$this->root->vars)) return;
	
		$this->root->vars[$this->lvalue] = $this->node;

	} // setLvalue()


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
			
			if ($node->selected) continue;
			if ($this->getNodeWeight($node) == 0) continue;

			$result = true;
			foreach ($this->filters as $filterKey => $filterValue) {
				$result &= $this->checkMatch($filterKey,$filterValue,$node);
			} // foreach filter

			if ($result) $this->matchList[] = $node;

		} // foreach node

		if (sizeof($this->matchList) == 0) {
			throw new Exception("selector does not match: \"" . $this->fullRef . "\"");
		}

	} // createMatchListResult()


	function checkMatch($filterKey,$filterValue,$node) {

		if (substr($filterValue,0,1) == '@') {
			$filterValue = $this->resolveMatchFilterValue($filterValue);
		}

		$matchType = substr($filterKey,0,1);				
		if (($matchType == '+') || ($matchType == '-')) {
			$filterKey = substr($filterKey,1);
		}
		if ($matchType != '-') $matchType = '+';
		
		if ($matchType == '-') {
			return $this->checkNegativeMatch($filterKey,$filterValue,$node);
		} // if neg 
		else {
			return $this->checkPositiveMatch($filterKey,$filterValue,$node);
		} // else pos

	} // checkMatch()


	function resolveMatchFilterValue($filterValue) {

		$a = explode(".",substr($filterValue,1));
		$id = trim($a[0]);
		$prop = trim($a[1]);
		if ($prop == "") $prop = "text";

		if (!array_key_exists($id,$this->root->vars)) {
			throw new Exception("Undefined filter value: " . $filterValue);		
		}

		$node = $this->root->vars[$id];
		$value = strtolower(trim($node->props[$prop][0]));

		return $value;
	} // resolveMatchFilterValue()


	function checkNegativeMatch($filterKey,$filterValue,$node) {

		if (!array_key_exists($filterKey,$node->props)) return true;

		foreach ($node->props[$filterKey] as $propIndex => $propValue) {
			if ($propValue == $filterValue) return false;
		} // foreach node props

		return true;	
	} // checkNegativeMatch()


	function checkPositiveMatch($filterKey,$filterValue,$node) {

		if (!array_key_exists($filterKey,$node->props)) return false;
		foreach ($node->props[$filterKey] as $propIndex => $propValue) {
			if (($propValue == '*') || ($propValue == $filterValue)) return true;
		} // foreach node props

		return false;
	} // checkPositiveMatch()


	function getNodeWeight(&$node) {

		if (array_key_exists($this->weightProp,$node->props)) {
			$weight = $node->props[$this->weightProp][0];
		} else {
			$weight = 100;
		}

		return $weight;
	} // getNodeWeight()


	function selectNode() {

		$this->node = null;

		$this->selectNodeFromCache();
		if ($this->node != null) return;
		$this->selectNodeFromDb();
		$this->setLvalue();

	} // selectNode()


	function selectNodeFromCache() {
		
		if ($this->lvalue != "") $name = $this->lvalue;

		if ($name == "") {
			if ($this->selectorType != '@') return;
			$a = explode("=",$this->selector);
			$name = $a[1];
		}

		if ($this->root->vars == null) return;
		if (!array_key_exists($name,$this->root->vars)) return;

		$this->node = $this->root->vars[$name];

	} // selectNodeFromCache()


	function selectNodeFromDb() {

		try {
			$this->createMatchList();
		} catch (Exception $e) {
			$this->renderAtom("ERROR(" . $this->selector . ") ");
			return;
		}

		$sumWeight = 0;
		foreach ($this->matchList as $node) {
			$sumWeight += $this->getNodeWeight($node);
		}

		$pick = rand(1,$sumWeight);

		$sumWeight = 0;
		foreach ($this->matchList as $node) {
			$sumWeight += $this->getNodeWeight($node);
			if ($pick <= $sumWeight) break;
		}
	
		if ($this->selectorType == '#') $node->selected = true;
		$this->node = $node;

	} // selectNodeFromDb()


} // class
?>
