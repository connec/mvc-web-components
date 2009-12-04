<?php

/**
 * Contains the TestSuite class for running a directory of tests.
 * 
 * @package mvc-web-components.testing
 * @author Chris Connelly
 */
namespace MVCWebComponents\UnitTest;
use MVCWebComponents\MVCException, MVCWebComponents\Inflector;

set_exception_handler(array('MVCWebComponents\UnitTest\TestSuite', 'handleException'));

/**
 * The TestSuite class provides a mechanism for running an entire directory of tests.
 * 
 * To run with all default settings simply call TestSuite::runTests() from a file in your test directory.
 * 
 * Additional functionality can be gained by extending from TestSuite, such as test preconditions.
 * 
 * @version 1.0
 */
class TestSuite {
	
	/**
	 * An array of tests found by findTests.
	 * 
	 * @var array
	 * @since 1.0
	 */
	public static $tests = array();
	
	/**
	 * An array of passed tests.
	 * 
	 * @var array
	 * @since 1.0
	 */
	public static $passedTests = array();
	
	/**
	 * An array of remaining tests.
	 * 
	 * @var array
	 * @since 1.0
	 */
	public static $remainingTests = array();
	
	/**
	 * An array describing the current test.
	 * 
	 * <code>
	 * array (
	 *    'test' => 'CurrentTest',
	 *    'subtest' => 'TestXAndY'
	 * )
	 * </code>
	 * 
	 * @var array
	 * @since 1.0
	 */
	public static $currentTest = array('test' => 'none', 'subtest' => 'none');
	
	/**
	 * Method stub for preTesting hook.
	 * 
	 * This hook is ran before any unit tests are ran.
	 * 
	 * @return void
	 * @since 1.0
	 */
	public static function preTesting() {}
	
	/**
	 * Method stub for preTest hook.
	 * 
	 * This hook is ran before EVERY test, useful to ensure a common testing environment across all the unit tests.
	 * 
	 * @return void
	 * @since 1.0
	 */
	public static function preTest() {}
	
	/**
	 * Method stub for postTest hook.
	 * 
	 * This hook is ran after EVERY test, useful to ensure certain post test conditions.
	 * 
	 * @return void
	 * @since 1.0
	 */
	public static function postTest() {}
	
	/**
	 * Method stub for postTesting hook.
	 * 
	 * This hook is ran after all the unit tests have been ran.
	 * 
	 * @return void
	 * @since 1.0
	 */
	public static function postTesting() {}
	
	/**
	 * Runs the named hook.
	 * 
	 * @param string $hook The name of the hook to run.
	 * @return void
	 * @since 1.0
	 */
	public static function runHook($hook, $subtest = false) {
		
		if($subtest) self::$currentTest['subtest'] = $hook;
		else self::$currentTest['test'] = $hook;
		
		static::$hook();
		
		if($subtest) self::$currentTest['subtest'] = 'none';
		else self::$currentTest['test'] = 'none';
		
	}
	
	/**
	 * Scans the current directory for '*_test.php' files and runs the unit test within its namespace.
	 * 
	 * @return void
	 * @since 1.0
	 */
	public static function runTests() {
		
		// Find our tests.
		self::findTests();
		
		// Call the preTesting hook.
		static::runHook('preTesting');
		
		// Start looping while there's tests to be done!
		while(self::$remainingTests != array()) {
			// Keep the old array of passed tests for checking we're not stuck in a loop.
			$check = self::$passedTests;
			
			foreach(self::$remainingTests as $test) {
				$test = self::$tests[$test];
				
				// Check if the prerequisites have passed.
				if(array_diff($test->dependencies, self::$passedTests) != array()) continue;
				else self::$currentTest['test'] = (string)$test;
				
				echo "<b>$test</b>";
				
				// Run the preTest hook.
				static::runHook('preTest', true);
				
				// And the test
				$test->run();
				
				// And the postTest hook...
				static::runHook('postTest', true);
				
				// Everything went smoothly, update the various arrays.
				self::$passedTests[] = (string)$test;
				self::$remainingTests = array_diff(
					array_map(function($test) { return (string)$test; }, self::$tests),
					self::$passedTests);
			}
			
			// Check that at least one test has passed, otherwise we're caught in a loop.
			if(self::$passedTests == $check) throw new MVCException('Unable to complete testing, this is likely due to an invalid dependency configuration.  Please ensure your dependency tests actually exist and there are no circular dependencies.  Test remaining: ' . implode(', ', self::$remainingTests) . '.');
		}
		
		// Do any final stuff.
		static::runHook('postTesting');
		
	}
	
	/**
	 * Finds the tests in this directory.
	 * 
	 * @return void
	 * @since 1.0
	 */
	protected static function findTests() {
		
		// Scan the current directory for a list of test files...
		$testFiles = array_filter(scandir('.'), function($file) {
			if(substr($file, -9) == '_test.php' and is_file($file)) return true;
			else return false;
		});
		sort($testFiles); // Reset the indexing.
		
		// Create an array of tests names from that...
		foreach($testFiles as $file) {
			require_once $file;
			
			$name = Inflector::camelize(substr($file,0,-4));
			$class = "$name\\$name";
			
			if(!class_exists($class)) throw new MVCException("Could not load test '$name' for file '$file'.  Please ensure your test is named correctly and in the correct namespace.");
			self::$tests[$name] = new $class;
			self::$remainingTests[] = (string)self::$tests[$name];
			if(!(self::$tests[$name] instanceof UnitTest)) throw new MVCException('Tests must extend UnitTest to be compatible with TestSuite.');
		}
		
	}
	
	/**
	 * Asserts a given value is strictly true.
	 * 
	 * @param mixed $value The value to check.
	 * @return void
	 * @since 1.0
	 */
	public static function assertTrue($value) {
		
		if($value !== true) throw new TestFailedException('true', $value);
		
	}
	
	/**
	 * Asserts a given value is false.
	 * 
	 * @param mixed $value The value to check.
	 * @return void
	 * @since 1.0
	 */
	public static function assertFalse($value) {
		
		if($value !== false) throw new TestFailedException('false', $value);
		
	}
	
	/**
	 * Asserts two given values are strictly equal.
	 * 
	 * @param mixed $value1
	 * @param mixed $value2
	 * @return void
	 * @since 1.0
	 */
	public static function assertStrict($value1, $value2) {
		
		if($value1 !== $value2) throw new TestFailedException('strict', $value1, $value2);
		
	}
	
	/**
	 * Asserts two given values are loosely equal.
	 * 
	 * @param mixed $value1
	 * @param mixed $value2
	 * @return void
	 * @since 1.0
	 */
	public static function assertEqual($value1, $value2) {
		
		if($value1 != $value2) throw new TestFailedException('equal', $value1, $value2);
		
	}
	
	/**
	 * Prints out a pretty exception.
	 * 
	 * @param Exception $exception The raised exception.
	 * @return void
	 * @since 1.0
	 */
	public static function handleException($exception) {
		
		if($exception instanceof MVCException) echo $exception->getFormattedMsg();
		else echo str_replace("\n", '<br />', $exception->getMessage());
		exit;
		
	}
	
}

/**
 * An exception thrown when a test fails.
 * 
 * @version 1.1
 */
class TestFailedException extends MVCException{
	
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
		
		// Print a line saying the test failed.
		echo '<li>' . TestSuite::$currentTest['subtest'] . ' <span style="color: red">failed</span>.</li></ul>';
		
		// Set the message.
		$this->message = TestSuite::$currentTest['test'] . '::' . TestSuite::$currentTest['subtest'] . ' failed: ' . Inflector::variablize("assert_$type") . ' - ';
		$value1 = $this->getPrintable($value1);
		$value2 = $this->getPrintable($value2);
		switch($type) {
			case 'strict':
				$this->message .= "$value1 <b>not strictly equal</b> $value2";
				break;
			case 'equal':
				$this->message .= "$value1 <b>not equal</b> $value2";
				break;
			case 'true':
				$this->message .= "$value1 <b>not strictly true</b>";
				break;
			case 'false':
				$this->message .= "$value1 <b>not strictly false</b>";
				break;
		}
		
	}
	
}

?>