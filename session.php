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
 * @version 1.1
 */
class Session extends Set {
	
	/**
	 * Overide Set's default behaviour to use $_SESSION instead of an empty array.
	 * 
	 * @return void
	 * @since 1.1
	 */
	protected static function setInit() {
		
		parent::__init();
		if(!isset(static::p()->register)) static::p()->register =& $_SESSION;
		
	}
	
}

?>