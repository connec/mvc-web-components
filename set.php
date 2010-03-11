<?php

/**
 * Contains the Set class.
 * 
 * @package utilities
 * @author Chris Connelly
 */
namespace MVCWebComponents;

/**
 * Provides functions for using dot paths.
 * 
 * Extensible, static class for reading and writing to an array (or ArrayAccess-able object) using a 'dot path' such as 'user.name' etc.
 * 
 * @version 1.1
 */

abstract class Set extends ExtensibleStatic {
	
	/**
	 * Initialize the registry variable.
	 * 
	 * @return void
	 * @since 1.1
	 */
	protected static function setInit() {
		
		parent::__init();
		if(!isset(static::p()->register)) static::p()->register = array();
		
	}
	
	/**
	 * Writes $value to the key specified by $path.
	 * 
	 * @param string $path The path to the key to write to.
	 * @param mixed $value The value to write.
	 * @return void
	 * @since 1.1
	 */
	public static function write($path, $value) {
		
		static::setInit();
		
		$a =& static::p()->register;
		$path = explode('.', $path);
		while($key = array_shift($path)) {
			if(is_numeric($key) and intval($key) >= 0) $key = intval($key);
			if(empty($path)) $a[$key] = $value;
			else {
				if(!isset($a[$key])) $a[$key] = array();
				$a =& $a[$key];
			}
		}
		
	}
	
	/**
	 * Return the value in the key given by $path.
	 * 
	 * @param string $path
	 * @return mixed
	 * @since 1.1
	 */
	public static function read($path) {
		
		static::setInit();
		
		$a =& static::p()->register;
		$path = explode('.', $path);
		while($key = array_shift($path)) {
			if(is_numeric($key) and intval($key) >= 0) $key = intval($key);
			if(!isset($a[$key])) throw new MissingKeyException($key);
			if(empty($path)) return $a[$key];
			$a =& $a[$key];
		}
		
	}
	
	/**
	 * Checks if the key given by $path is set.
	 * 
	 * @param string $path
	 * @return bool True if the key given by $path is set, false otherwise.
	 * @since 1.1
	 */
	public static function check($path) {
		
		static::setInit();
		
		$a =& static::p()->register;
		$path = explode('.', $path);
		while($key = array_shift($path)) {
			if(is_numeric($key) and intval($key) >= 0) $key = intval($key);
			if(!isset($a[$key])) return false;
			if(empty($path)) return isset($a[$key]);
			$a =& $a[$key];
		}
		
	}
	
	/**
	 * Unsets the key given by $path.
	 * 
	 * @param string $path
	 * @return void
	 * @since 1.1
	 */
	public static function clear($path) {
		
		static::setInit();
		
		$a =& static::p()->register;
		$path = explode('.', $path);
		while($key = array_shift($path)) {
			if(is_numeric($key) and intval($key) >= 0) $key = intval($key);
			if(!isset($a[$key])) throw new MissingKeyException($key);
			if(empty($path)) unset($a[$key]);
			else $a =& $a[$key];
		}
		
	}
	
	/**
	 * Set (or it's children) cannot be instantiated.
	 * 
	 * @return void
	 * @since 1.1
	 */
	private final function __construct() {}
	
	/**
	 * Set (or it's children) cannot be cloned.
	 * 
	 * @return void
	 * @since 1.1
	 */
	private final function __clone() {}
	
}

/**
 * An exception thrown when attempting to read from a non-existant key.
 * 
 * @subpackage exceptions
 * @version 1.0
 */
class MissingKeyException extends MVCException {
	
	/**
	 * Set the message with respect to a given invalid key.
	 * 
	 * @param string $key
	 * @return void
	 * @since 1.0
	 */
	public function __construct($key) {
		
		$this->message = "Attempted to access non-existant key `$key`.";
		
	}
	
}

?>