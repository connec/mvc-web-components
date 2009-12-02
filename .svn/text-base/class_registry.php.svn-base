<?php

/**
 * Contains ClassRegistry class and related classes.
 *
 * @package utilities
 * @author Chris Connelly
 */
namespace MVCWebComponents;

/**
 * Provides access to registered objects irrespective of scope.
 *
 * A class to store instances of registered classes to allow easy access from any scope.  Similar functionality can be gained by extending the Singleton class.
 *
 * @version 1.0
 * @see Singleton
 */
class ClassRegistry {
	
	/**
	 * Associative array of class instances.
	 *
	 * Format:
	 * $register = array('ClassName' => [Class Instance]);
	 *
	 * @var array
	 * @since 1.0
	 */
	protected static $register = array();
	
	/**
	 * Check if a class is in the register.
	 *
	 * @param string $className The name of a class to check for.
	 * @return bool True if the class exists, false otherwise.
	 * @since 1.0
	 */
	public static function exists($class) {
		
		return isset($register[$class]);
		
	}
	
	/**
	 * Register a class in the registry.
	 *
	 * @param mixed $class The name of a class/class instance to register.
	 * @return void
	 * @since 1.0
	 */
	public static function register($class) {
		
		if(is_object($class)) self::$register[get_class($class)] =& $class;
		else self::$register[$class] = new $class;
		
	}
	
	/**
	 * Retrieve an instance of a class from the registry.
	 *
	 * @param string $className The name of the class to retrieve.
	 * @return object The instance of class $className in the registry.
	 * @since 1.0
	 */
	public static function &get($className) {
		
		if(!self::exists($className)) self::register($className);
		return self::$register[$className];
		
	}
	
	/**
	 * Delete an instance from the register.
	 *
	 * @param string $className The name of the class to remove from the register.
	 * @return void
	 * @since 1.0
	 */
	public static function remove($className) {
		
		if(self::exists($className)) unset(self::$register[$className]);
		
	}
	
}

?>