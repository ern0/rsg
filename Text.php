<? 
Class Text {

	function __construct($fnam) {
		$this->fileName = $fnam;
		$this->lines = [];
	} // ctor()


	function load(&$lines = null) {

		if ($lines == null) $lines = &$this->lines;
		
		$file = fopen($this->fileName,"r");
		while (!feof($file)) {

			$line = rtrim(fgets($file));
			if ($line == "") continue;
			if ($this->checkAndProcessIncludeCommand($line)) continue;

			$lines[] = $line;

		} // while not eof

	} // load()


	function checkAndProcessIncludeCommand($line) {

		if (ord($line[0]) <= 32) return false;

		$a = explode(" ",$line);
		if ($a[0] != "include") return false;

		$inc = new Text($a[1]);
		$inc->load($this->lines);

		return true;
	} // checkAndProcessIncludeCommand()


	function dump() {
		foreach ($this->lines as $line) {
			echo($line . "\n");
		}
	} // dump()

} // class Text 
?>
