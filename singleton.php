<?php

/**
 * Contains the abstract Singleton class.
 *
 * @package utilities
 * @author Chris Connelly
 */
namespace MVCWebComponents;

/**
 * Abstract Singleton class to provide zero-configuration singleton functionality.
 *
 * @version 1.0
 */

abstract class Singleton {
	
	/**
	 * Contains an array of instances to be served on getInstance calls.
	 *
	 * @var array
	 * @since 1.0
	 */
	private static $instances = array();
	
	/**
	 * Protect the constructor so it cannot be called with 'new'.
	 * 
	 * @return void
	 * @since 1.0
	 */
	protected function __construct() {}
	
	/**
	 * Protect the clone method so it cannot be cloned with 'clone'.
	 * 
	 * @return void
	 * @since 1.0
	 */
	final private function __clone() {}
	
	/**
	 * Returns an instance of the class that calls the function (child classes).
	 * 
	 * @return object An instance of the calling class, by reference.
	 * @since 1.0
	 */
	final public static function &instance() {
		
		$class = get_called_class();
		if(!isset(self::$instances[$class])) self::$instances[$class] = new $class;
		return self::$instances[$class];
		
	}
	
}

?>