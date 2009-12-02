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
 * Extensible, static class for reading and writing to an array/object (objects are read-only) using a 'dot path' such as 'user.name' etc.
 * 
 * @version 1.0
 */

Class Set {
	
	/**
	 * Writes $value to the key in $array specified by $path.
	 *
	 * @param array $array An array to write to.
	 * @param string $path The 'dot path' to the key to write to.
	 * @param mixed $value The data to write to $path in $array.
	 * @return bool True on success, false on failure.
	 * @since 1.0
	 */
	public static function writePath(&$array, $path, $value) {
		
		if(!is_array($path)) {
			$path = explode('.', $path);
		}
		
		$_array = &$array;
		
		foreach($path as $i => $key) {
			if(is_numeric($key) and intval($key) > 0 or $key == '0') {
				$key = intval($key);
			}
			if($i == count($path) - 1) {
				$_array[$key] = $value;
				return true;
			}else {
				if(!isset($_array[$key])) {
					$_array[$key] = array();
				}
				$_array =& $_array[$key];
			}
		}
		
		return false;
		
	}
	
	/**
	 * Returns the value in $array represented by $path.
	 *
	 * @param mixed $array The array (or object) to read from.
	 * @param string $path The path to the key in $array to read from.
	 * @return mixed The data stored in $array represented by $path.
	 * @since 1.0
	 */
	public static function readPath($array, $path) {
		
		if(!is_array($path)) {
			$path = explode('.', $path);
		}
		if(is_numeric($path[0]) and intval($path[0]) >= 0) {
			$path[0] = intval($path[0]);
		}
		
		if(count($path) == 1) {
			if(is_array($array))
				return $array[$path[0]];
			else
				return $array->{$path[0]};
		}else {
			if(isset($array[$path[0]])) {
				return Set::read($array[array_shift($path)], $path);
			}elseif(!empty($array->{$path[0]})) {
				array_shift($path);
				return Set::read($array{$path[0]}, $path);
			}else {
				return null;
			}
		}
		
	}
	
	/**
	 * Clears the key in $array represented by $path.
	 *
	 * @param mixed $array The array to clear from.
	 * @param string $path The path to the key in $array to clear.
	 * @return array The array with the result cleared.
	 * @since 1.0
	 */
	public static function clearPath(&$array, $path) {
		
		if(!is_array($path)) {
			$path = explode('.', $path);
		}
		
		$_array =& $array;
		
		foreach($path as $i => $key) {
			if(is_numeric($key) and intval($key) > 0 or $key == '0') {
				$key = intval($key);
			}
			if($i == count($path) - 1) {
				unset($_array[$key]);
			}else {
				if(!isset($_array[$key])) {
					return $array;
				}
				$_array =& $_array[$key];
			}
		}
		
		return $array;
		
	}
	
	/**
	 * Returns whether or not the kay represented by $path is set in $array.
	 *
	 * @param mixed $array The array (or object) to check.
	 * @param string $path The path to the key in $array to check.
	 * @return bool True if the key is set, false otherwise.
	 * @since 1.0
	 */
	public static function checkPath($array, $path) {
		
		if(!is_array($path)) {
			$path = explode('.', $path);
		}
		
		foreach($path as $i => $key) {
			if(is_numeric($key) and intval($key) > 0 or $key == '0') {
				$key = intval($key);
			}
			if($i == count($path) - 1) {
				if(is_array($array))
					return isset($array[$key]);
				else
					return isset($array->{$key});
			}else {
				if(is_array($array)) {
					if(!isset($array[$key])) {
						return false;
					}
					$array =& $array[$key];
				}else {
					if(!isset($array->{$key})) {
						return false;
					}
					$array =& $array->{$key};
				}
			}
		}
		return false;
		
	}
	
}

?>