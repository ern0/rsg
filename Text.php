<? 
Class Text {

	function __construct($fnam) {

		$this->base = $this;

		$this->fileName = $fnam;
		$this->nodes = [];
		$this->lastNode = null;
	
	} // ctor()


	function addLine($line) {

		$node = $this->base->lastNode;
		if ($node == null) fatal("missing node def");

		$node->procText($line);

	} // addLine()


	function load() {
		
		$file = fopen($this->fileName,"r");
		if (!$file) fatal("file open error: " . $this->fileName);
		while (!feof($file)) {

			$line = rtrim(fgets($file));
			if (trim($line) == "") continue;
			if ($this->checkAndProcessCommand($line)) continue;

			$this->addLine($line);

		} // while not eof

	} // load()


	function checkAndProcessCommand($line) {

		if (ord($line[0]) <= 32) return false;

		$a = explode(" ",$line);
		if ($a[0] == "include") {
			$this->processIncludeCommand($a[1]);
			return true;
		}

		if ($a[0] == "node") {
			$this->processNodeCommand($line);
			return true;
		}

		fatal("invalid command: \"" . $a[0] . "\"");

		return false;
	} // checkAndProcessCommand()


	function processIncludeCommand($fnam) {

		$inc = new Text($fnam);
		$inc->base = &$this;
		$inc->load();

	} // processIncludeCommand()


	function processNodeCommand($line) {

		$index = sizeof($this->base->nodes);
		$this->base->nodes[$index] = new Node($line);
		$this->base->lastNode = &$this->base->nodes[$index];

	} // processNodeCommand()


	function dump() {

		foreach ($this->base->nodes as $node) {
			$node->dump();
		}

	} // dump()

} // class Text 
?>
