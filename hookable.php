<?php

/**
 * Contains the Hookable class.
 * 
 * @package mvc-web-components
 * @author Chris Connelly
 */
namespace MVCWebComponents;

/**
 * Abstract class to allow arbitrary 'hooks' to be defined 
 * and ran throughout the child class.
 * 
 * @version 1.0
 */
abstract class Hookable extends ExtensibleStatic {
	
	/**
	 * Add a callback to a particular hook.
	 * 
	 * @param string $name The name of the hook.
	 * @param callback $callback The callback to register.
	 * @return void
	 * @since 1.0
	 */
	public static function addHook($name, $callback) {
		
		if(!is_callable($callback)) {
			if($callback[0] == '$this') {
				$class = get_called_class();
				if(!is_callable(array($class, $callback[1])))
					throw new BadCallbackException(array($class, $callback[1]));
			} else throw new BadCallbackException($callback);
		}
		
		static::hookInit();
		
		if(!isset(static::p()->hooks[$name]))
			static::p()->hooks[$name] = array();
		static::p()->hooks[$name][] = $callback;
		
	}
	
	/**
	 * Executes all the callbacks of a particular hook.
	 * 
	 * @param string $name
	 * @param object $_this If this is passed the callback using '$this' as the object name will use the provided object instead.
	 * @param array  $args
	 * @param bool   $required If true and there are no callbacks under $name, throws an exception.
	 * @return array An array of the return values of the callbacks.
	 * @since 1.0
	 */
	protected static function runHook($name, $_this = null, $args = array(), $required = false) {
		
		static::hookInit();
		
		$return = array();
		if(isset(static::p()->hooks[$name]) and !empty(static::p()->hooks[$name])) {
			foreach(static::p()->hooks[$name] as $callback) {
				if(is_array($callback) and $callback[0] == '$this' and is_object($_this)) $callback[0] =& $_this;
				$return[] = call_user_func_array($callback, $args);
			}
		}elseif($required) throw new MissingHookException($name);
		
		// Call any parent hooks as well.
		$parent = get_parent_class(get_called_class());
		if(is_subclass_of($parent, __CLASS__))
			$return = array_merge($return, $parent::runHook($name, $_this, $args, $required));
		
		return $return;
		
	}
	
	/**
	 * Initializes the hooks array.
	 * 
	 * @return void
	 * @since 1.0
	 */
	protected static function hookInit() {
		
		if(!isset(static::p()->hooks)) {
			static::p()->hooks = array();
			
			if(!isset(static::$hooks) or !is_array(static::$hooks)) return;
			
			foreach(static::$hooks as $hook) {
				if(isset(static::$$hook) and is_array(static::$$hook)) {
					foreach(static::$$hook as $callback) {
						static::addHook($hook, $callback);
					}
				}
			}
		}
		
	}
	
}

/**
 * Exception thrown when {@link Hookable::addHook()} encounters a bad callback.
 * 
 * @version 1.0
 */
class BadCallbackException extends MVCException {
	
	/**
	 * Set the message.
	 * 
	 * @param callback $callback The bad callback.
	 * @return void
	 * @since 1.0
	 */
	public function __construct($callback) {
		
		ob_start();
		var_dump($callback);
		$dump = ob_get_clean();
		
		$this->message = "Bad callback given to addHook: $dump.";
		
	}
	
}

/**
 * Exception thrown when {@link Hookable::runHook()} encounters a missing hook.
 * 
 * @version 1.0
 */
class MissingHookException extends MVCException {
	
	/**
	 * Set the message.
	 * 
	 * @param string $hook The name of the missing hook.
	 * @return void
	 * @since 1.0
	 */
	public function __construct($name) {
		
		$this->message = "Missing hook '$name'.";
		
	}
	
}

?>