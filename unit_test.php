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
	 * A flag telling if a test is unfinished (an unfinished test will fail).
	 * 
	 * @var bool
	 * @since 1.2
	 */
	public $unfinished = false;
	
	/**
	 * Converting a test to a string should return its name.
	 * 
	 * @return string The name of the test.
	 * @since 1.2
	 */
	public function __toString() {
		
		return @end(explode('\\', get_class($this)));
		
	}
	
	/**
	 * Performs the testing.
	 * 
	 * @return void
	 * @since 1.0
	 */
	public function run() {
		
		if($this->unfinished) throw new MVCException("$this failed.  Test unfinished.");
		if(!$this->preTesting()) throw new MVCException(get_class($this) . ' error: Setup failed.');
		echo '<ul>';
		foreach(get_class_methods($this) as $method) {
			if(stripos($method, 'test') !== 0) continue;
			if(!$this->preTest()) throw new MVCException(get_class($this) . " error: Setup failed for test '$method'.");
			self::$currentTest = "$this::$method";
			$this->$method();
			echo "<li>$method <span style=\"color: #090;\">passed</span>.</li>";
			if(!$this->postTest()) throw new MVCException(get_class($this) . " error: Cleanup failed for test '$method'.");
		}
		if(!$this->postTesting()) throw new MVCException(get_class($this) . ' error: Cleanup failed.', 1);
		
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
			return new $class;
		}, $testFiles);
		
		// Maintain an array of completed tests.
		$ranTests = array();
		
		// Go through the array(s), load the file and execute the test.
		while(array_diff($tests, $ranTests) != array()) {
			foreach($testFiles as $i => $file) {
				if(array_diff($tests[$i]->dependencies, $ranTests) != array()) continue;
				
				echo '<h1>' . $tests[$i] . '</h1>';
				$tests[$i]->run();
				
				$ranTests[] =& $tests[$i];
				unset($testFiles[$i]);
			}
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
		
		if(is_bool($value)) return $value ? 'true' : 'false';
		elseif(is_array($value) or is_object($value)) return print_r($value, true);
		else return strval($value);
		
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
		
		$this->message = UnitTest::$currentTest . ' failed: ' . Inflector::variablize("assert_$type") . ' - ';
		switch($type) {
			case 'strict':
				$this->message .= UnitTest::getPrintable($value1) . ' not strictly equal ' . UnitTest::getPrintable($value2);
				break;
			case 'equal':
				$this->message .= UnitTest::getPrintable($value1) . ' not equal ' . UnitTest::getPrintable($value2);
				break;
			case 'true':
				$this->message .= UnitTest::getPrintable($value1) . ' not strictly true.';
				break;
			case 'false':
				$this->message .= UnitTest::getPrintable($value1) . ' not strictly false.';
				break;
		}
		
	}
	
}

?>