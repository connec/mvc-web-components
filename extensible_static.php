<?php

/**
 * Contains the abstract ExtensibleStatic class.
 * 
 * @package mvc-web-components
 * @author Chris Connelly
 */
namespace MVCWebComponents;

/**
 * ExtensibleStatic class simulates inheritance for static properties.
 * 
 * @version 1.0
 */
abstract class ExtensibleStatic {
	
	/**
	 * An array of class states.
	 * 
	 * @var array
	 * @since 1.0
	 */
	protected static $states = array();
	
	/**
	 * Returns an object representation of the static class' state.
	 * 
	 * @return object
	 * @since 1.0
	 */
	protected static function &properties() {
		
		static::__init();
		return static::$states[get_called_class()];
		
	}
	
	/**
	 * Alias of {@link properties} for convenience.
	 * 
	 * @return object
	 * @since 1.0
	 */
	protected static function &p() {
		
		return static::properties();
		
	}
	
	/**
	 * Initialize the class to allow recording the state.
	 * 
	 * @return void
	 * @since 1.0
	 */
	protected static function __init() {
		
		if(!isset(static::$states[get_called_class()])) static::$states[get_called_class()] = new \StdClass;
		
	}
	
}

?>