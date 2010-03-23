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
 * @version 1.2
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
	 * Gets a reference to the last array element before the final 'key'.
	 * 
	 * @param  string $path
	 * @param  string $create What to do when a missing key is encountered (error, create or return false).
	 * @return array  A reference to the last array in $path.
	 * @since  1.2
	 */
	protected static function &getLastArray($path, $create = 'error') {
		
		static::setInit();
		
		$false = false; // avoid strict standard errors regarding passing by ref.
		
		$a =& static::p()->register;
		$path = explode('.', $path);
		while($key = array_shift($path) or $key !== null) { // second comparison avoids ignoring '0' keys
			if(is_numeric($key) and intval($key) >= 0) $key = intval($key);
			if(!isset($a[$key])) {
				if($create == 'error') throw new MissingKeyException($key);
				elseif($create == 'create') $a[$key] = array();
				else return $false;
			}
			if(empty($path)) return $a;
			$a =& $a[$key];
		}
		
	}
	
	/**
	 * Gets the last key from a path.
	 * 
	 * @param  string $path
	 * @return string
	 * @since  1.2
	 */
	protected static function getLastKey($path) {
		
		return @end(explode('.', $path));
		
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
		$a =& static::getLastArray($path, 'create');
		$a[static::getLastKey($path)] = $value;
		
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
		$a = static::getLastArray($path);
		return $a[static::getLastKey($path)];
		
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
		$a =& static::getLastArray($path, 'return');
		return $a === false ? false : true;
		
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
		$a =& static::getLastArray($path);
		unset($a[static::getLastKey($path)]);
		
	}
	
	/**
	 * Append some data to the contents of the key given by $path.
	 * 
	 * @param string $path
	 * @return void
	 * @since 1.2
	 */
	public static function append($path, $value) {
		
		static::setInit();
		$a =& static::getLastArray($path);
		$v =& $a[static::getLastKey($path)];
		
			if(is_array($v))  $v[] = $value;
		elseif(is_string($v)) $v .= strval($value);
		elseif(is_int($v))    $v += intval($value);
		elseif(is_float($v))  $v += floatval($value);
		
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