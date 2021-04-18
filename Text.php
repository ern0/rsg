<? 
Class Text {

	function __construct($fnam) {
		$this->fileName = $fnam;
		$this->lines = [];
		$this->base = $this;
	} // ctor()


	function addLine($line) {
		$this->base->lines[] = $line;
	} // addLine()


	function load() {
		
		$file = fopen($this->fileName,"r");
		if (!$file) fatal("file open error: " . $this->fileName);
		while (!feof($file)) {

			$line = rtrim(fgets($file));
			if ($line == "") continue;
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

		return false;
	} // checkAndProcessCommand()


	function processIncludeCommand($fnam) {

		$inc = new Text($fnam);
		$inc->base = $this;
		$inc->load();

	} // processIncludeCommand()


	function processNodeCommand($line) {

		$this->addLine($line);

	} // processNodeCommand()


	function dump() {
		foreach ($this->lines as $line) {
			echo($line . "\n");
		}
	} // dump()

} // class Text 
?>
