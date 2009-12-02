<?php

/**
 * Contains the session class.
 * 
 * @package utilities
 * @author Chris Connelly
 */
namespace MVCWebComponents;

/**
 * Manages reading from/writing to $_SESSION.
 * 
 * @version 1.0
 */

Class Session extends Set {
	
	/**
	 * Writes some data to the session array $_SESSION.
	 *
	 * @param string $key A key in the session array to be used to store $data - will be created if it doesn't exist.
	 * @param mixed $data Data to store in $_SESSION[$key].
	 * @return bool True if the write was successful, false otherwise.
	 * @since 1.0
	 */
	public static function write($key, $data) {
		
		return parent::writePath($_SESSION, $key, $data);
		
	}
	
	/**
	 * Reads some data from the session array $_SESSION.
	 *
	 * @param string $key A key in the session array to read from.
	 * @return mixed The value stored in $key in $_SESSION.
	 * @since 1.0
	 */
	public static function read($key) {
		
		return parent::readPath($_SESSION, $key);
		
	}
	
	/**
	 * Clears the value from $_SESSION represented by $key.
	 *
	 * @param string $key The key in $_SESSION to clear.
	 * @return bool Whether or not the clearing succeeded.
	 * @since 1.0
	 */
	public static function clear($key) {
		
		return parent::clearPath($_SESSION, $key);
		
	}
	
	/**
	 * Checks if the value in $_SESSION represented by $key is set.
	 *
	 * @param string $key The key in $_SESSION to check.
	 * @return bool True if $key is set in $_SESSION, false otherwise.
	 * @since 1.0
	 */
	public static function check($key) {
		
		return parent::checkPath($_SESSION, $key);
		
	}
	
}

?>