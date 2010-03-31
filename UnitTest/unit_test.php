<?php

/**
 * Contains the UnitTest class and other related classes.
 * 
 * @package mvc-web-components.testing
 * @author Chris Connelly
 */
namespace MVCWebComponents\UnitTest;
use MVCWebComponents\MVCException, MVCWebComponents\Inflector;

/**
 * The UnitTest class provides useful functionality for implementing unit tests.
 * 
 * @version 1.3
 */
abstract class UnitTest {
	
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
	 * Called before any subtests are ran.
	 * 
	 * @return void
	 * @since 1.0
	 */
	public function preTesting() {}
	
	/**
	 * Called after all subtests are ran.
	 * 
	 * @return void
	 * @since 1.0
	 */
	public function postTesting() {}
	
	/**
	 * Called before each subtest.
	 * 
	 * @return void
	 * @since 1.0
	 */
	public function preTest() {}
	
	/**
	 * Called after each subtest.
	 * 
	 * @return void
	 * @since 1.0
	 */
	public function postTest() {}
	
	/**
	 * Run the named hook.
	 * 
	 * @param string $hook The name of the hook to run.
	 * @since 1.3
	 */
	public function runHook($hook) {
		
		TestSuite::$currentTest['subtest'] = $hook;
		$this->$hook();
		TestSuite::$currentTest['subtest'] = 'none';
		
	}
	
	/**
	 * Performs this test.
	 * 
	 * @return void
	 * @since 1.0
	 */
	public function run() {
		
		$this->runHook('preTesting');
		
		echo '<ul>';
		
		foreach(get_class_methods($this) as $method) {
			if(stripos($method, 'test') !== 0) continue;
			
			$this->runHook('preTest');
			
			TestSuite::$currentTest['subtest'] = $method;
			$this->$method();
			
			// Print a success message.
			$method = Inflector::humanize(Inflector::underscore($method));
			echo "<li>$method <span style=\"color: #090;\">passed</span>.</li>";
			
			$this->runHook('postTest');
		}
		$this->runHook('postTesting');
		
		echo '</ul>';
		
	}
	
	/**
	 * Asserts that a given value is strictly true.
	 * 
	 * @param mixed $check The value to ensure is true.
	 * @return bool True if $check is true.
	 * @since 1.0
	 */
	public function assertTrue($check) {
		
		TestSuite::assertTrue($check);
		
	}
	
	/**
	 * Asserts that a given value is strictly false.
	 * 
	 * @param mixed $check The value to ensure is false.
	 * @return bool True if $check is false.
	 * @since 1.0
	 */
	public function assertFalse($check) {
		
		TestSuite::assertFalse($check);
		
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
		
		TestSuite::assertEqual($value1, $value2);
		
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
		
		TestSuite::assertStrict($value1, $value2);
		
	}
	
}

?>