<?php

/**
 * Contains the UnitTest class and other related classes.
 * 
 * @package testing
 * @author Chris Connelly
 */
namespace MVCWebComponents;
use MVCWebComponents\MVCException;

set_exception_handler(array('MVCWebComponents\UnitTest', 'handleException'));

/**
 * The UnitTest class provides useful functionality for implementing unit tests.
 * 
 * @version 1.2
 */
abstract class UnitTest {
	
	/**
	 * Stores the name of the currently executing test.
	 * 
	 * @var string
	 * @since 1.0
	 */
	public static $currentTest;
	
	/**
	 * Stores an array of any tests this test is dependent on.
	 * 
	 * @var array
	 * @since 1.2
	 */
	public $dependencies = array();
	
	/**
	 * Converting a test to a string should return its name (sans namespace).
	 * 
	 * @return string The name of the test.
	 * @since 1.2
	 */
	public function __toString() {
		
		return @end(explode('\\', get_class($this)));
		
	}
	
	/**
	 * Performs this test.
	 * 
	 * @return void
	 * @since 1.0
	 */
	public function run() {
		
		self::$currentTest = 'preTesting';
		if(!$this->preTesting()) throw new MVCException("$this error: Setup failed.");
		
		echo '<ul>';
		foreach(get_class_methods($this) as $method) {
			if(stripos($method, 'test') !== 0) continue;
			
			self::$currentTest = "preTest ($method)";
			if(!$this->preTest()) throw new MVCException("$this error: Setup failed for test '$method'.");
			
			self::$currentTest = $method;
			$this->$method();
			echo "<li>$method <span style=\"color: #090;\">passed</span>.</li>";
			
			self::$currentTest = "postTest ($method)";
			if(!$this->postTest()) throw new MVCException("$this error: Cleanup failed for test '$method'.");
		}
		self::$currentTest = 'postTesting';
		if(!$this->postTesting()) throw new MVCException("$this error: Cleanup failed.");
		
		echo '</ul>';
		return true;
		
	}
	
	/**
	 * Scans the current directory for '*_test.php' files and runs the unit test within its namespace.
	 * 
	 * @return void
	 * @since 1.2
	 */
	public static function runTests() {
		
		// Scan the current directory for a list of test files...
		$testFiles = array_filter(scandir('.'), function($file) {
			if(substr($file, -9) == '_test.php' and is_file($file)) return true;
			else return false;
		});
		sort($testFiles); // Reset the indexing.
		
		// Create an array of test names from that...
		$tests = array_map(function($file) {
			require_once $file;
			
			$name = Inflector::camelize(substr($file,0,-4));
			$class = "\\$name\\$name";
			
			if(!class_exists($class)) throw new MVCException("Missing test '$class', please ensure all tests are the camelised version of the file name, and in a namespace equal to the test name.");
			$return = new $class;
			if(!($return instanceof UnitTest)) throw new MVCException("Unit tests must be extended from the UnitTest class.");
			return $return;
		}, $testFiles);
		
		// Maintain an array of completed tests.
		$ranTests = array();
		
		// Go through the array(s), load the file and execute the test.
		while(array_diff($tests, $ranTests) != array()) {
			$oldRanTests = $ranTests;
			foreach($testFiles as $i => $file) {
				if(array_diff($tests[$i]->dependencies, $ranTests) != array()) continue;
				
				echo '<h2>' . $tests[$i] . '</h2>';
				$tests[$i]->run();
				
				$ranTests[] =& $tests[$i];
				unset($testFiles[$i]);
			}
			
			// Check if we're infinite looping.
			if($ranTests == $oldRanTests) throw new MVCException("Bad dependency configuration in tests.  No valid dependency tree (possibly missing test).  Unable to run tests " . implode(', ', array_diff($tests, $ranTests)) . '.');
		}
		
	}
	
	/**
	 * Called before any tests are ran.
	 * 
	 * @return bool True if setup is successful and testing can continue, false otherwise.
	 * @since 1.0
	 */
	public function preTesting() {
		
		return true;
		
	}
	
	/**
	 * Called after all tests are ran.
	 * 
	 * @return bool True if cleanup is successful, false otherwise.
	 * @since 1.0
	 */
	public function postTesting() {
		
		return true;
		
	}
	
	/**
	 * Called before each test.
	 * 
	 * @return bool True if setup is successful and testing can continue, false otherwise.
	 * @since 1.0
	 */
	public function preTest() {
		
		return true;
		
	}
	
	/**
	 * Called after each test.
	 * 
	 * @return bool True if cleanup is cuessful and testing can continue, false otherwise.
	 * @since 1.0
	 */
	public function postTest() {
		
		return true;
		
	}
	
	/**
	 * Asserts that a given value is strictly true.
	 * 
	 * @param mixed $check The value to ensure is true.
	 * @return bool True if $check is true.
	 * @since 1.0
	 */
	public function assertTrue($check) {
		
		if($check === true) return true;
		else throw new TestFailedException('true', $check);
		
	}
	
	/**
	 * Asserts that a given value is strictly false.
	 * 
	 * @param mixed $check The value to ensure is false.
	 * @return bool True if $check is false.
	 * @since 1.0
	 */
	public function assertFalse($check) {
		
		if($check === false) return true;
		else throw new TestFailedException('false', $check);
		
	}
	
	/**
	 * Asserts that two given values are equal.
	 * 
	 * @param mixed $value1 The value to compare with $value2.
	 * @param mixed $value2 The value to compare with $value1.
	 * @return bool True when $value1 == $value2.
	 * @since 1.0
	 */
	public function assertEqual($value1, $value2) {
		
		if($value1 == $value2) return true;
		else throw new TestFailedException('equal', $value1, $value2);
		
	}
	
	/**
	 * Asserts that two given values are strictly equal, that is they are equal and of the same type.
	 * 
	 * @param mixed $value1 The value to compare with $value2.
	 * @param mixed $value2 The value to compare with $value1.
	 * @return bool True when $value === $value2.
	 * @since 1.1
	 */
	public function assertStrict($value1, $value2) {
		
		if($value1 === $value2) return true;
		else throw new TestFailedException('strict', $value1, $value2);
		
	}
	
	/**
	 * Prints out a pretty exception.
	 * 
	 * @return void
	 * @since 1.0
	 */
	public static function handleException($exception) {
		
		if($exception instanceof MVCException) echo $exception->getFormattedMsg();
		else echo str_replace("\n", '<br />', $exception->getMessage());
		exit;
		
	}
	
	/**
	 * Returns a string representation of a value for display.
	 * 
	 * @param mixed $value The value for which to return a string representation.
	 * @return string A string representation of $value.
	 * @since 1.1
	 */
	public static function getPrintable($value) {
		
		ob_start();
		var_dump($value);
		return '<div style="padding: 10px;border: 1px solid #666;background-color: #eee">' . ob_get_clean() . '</div>';
		
	}
	
}

/**
 * An exception thrown when a test fails.
 * 
 * @version 1.0
 */
class TestFailedException extends MVCException{
	
	/**
	 * Set the exceptions message.
	 * 
	 * @param string $type The type of the assert that failed.
	 * @param mixed $value1 The first value in the comparison.
	 * @param mixed $value2 The second value in the comparison (if one exists).
	 * @return void
	 * @since 1.0
	 */
	public function __construct($type, $value1, $value2 = '') {
		
		echo '<li>' . UnitTest::$currentTest . ' <span style="color: red">failed</span>.</li></ul>';
		$this->message = UnitTest::$currentTest . ' failed: ' . Inflector::variablize("assert_$type") . ' - ';
		switch($type) {
			case 'strict':
				$this->message .= UnitTest::getPrintable($value1) . ' <b>not strictly equal</b> ' . UnitTest::getPrintable($value2);
				break;
			case 'equal':
				$this->message .= UnitTest::getPrintable($value1) . ' <b>not equal</b> ' . UnitTest::getPrintable($value2);
				break;
			case 'true':
				$this->message .= UnitTest::getPrintable($value1) . ' <b>not strictly true</b>.';
				break;
			case 'false':
				$this->message .= UnitTest::getPrintable($value1) . ' <b>not strictly false</b>.';
				break;
		}
		
	}
	
}

?>