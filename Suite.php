<?
Class Suite {

	function __construct() {

		$this->totalCases = 0;
		$this->passedCases = 0;
		$this->failedCases = 0;

		$this->totalAssertions = 0;
		$this->passedAssertions = 0;
		$this->failedAssertions = 0;

	} // ctor()


	function assert($assertion, $message) {

		$this->totalAssertions++;
		if ($assertion) {
			$this->passedAssertions++;
			return;
		}
		$this->failedAssertions++;

		echo(
			"assertion failed: " 
			. get_class($this)
			. "/" 
			. $this->case
			. "/" 
			. $message 
			. "\n"
		);		 

	} // assert()


	function renderNumber($text, $value) {

		$plural = ( $value > 1 ? "s" : "" );
		$text = str_replace("~", $plural, $text);
		$text = str_replace("#", $value, $text);

		return $text;
	} // renderNumber()


	function main() {
		
		$methods = get_class_methods(get_class($this));
		foreach ($methods as $method) {
			if (substr($method,0,5) != "test_") continue;

			$this->totalCases++;
			$preFailed = $this->failedAssertions;
			$this->case = substr($method,5);

			$f = '$' . "this->" . $method . "();";
			eval($f);

			if ($preFailed == $this->failedAssertions) {
				$this->passedCases++;
			} else {
				$this->failedCases++;
			}

		} // foreach cases

		echo(
			get_class() . " - "
			. $this->renderNumber("case~: #, ",$this->totalCases)
			. $this->renderNumber("passed: #, ",$this->passedCases)
			. $this->renderNumber("failed: #, ",$this->failedCases)
			. $this->renderNumber("assert~: #, ",$this->totalAssertions)
			. $this->renderNumber("passed: #, ",$this->passedAssertions)
			. $this->renderNumber("failed: # ",$this->failedAssertions)
			. " - "
			. ( $this->failedAssertions == 0 ? "okay" : "FAIL" )
			. "\n"
		);

	} // main()

} // class
?>