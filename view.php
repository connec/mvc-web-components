<?php

/**
 * Contains the View class and related exceptions.
 * 
 * @package mvcwebcomponents
 * @author Chris Connelly
 */

namespace MVCWebComponents;

/**
 * Generates HTML code based on a template and given variables.
 * 
 * @version 0.4
 */
class View
{
	
	/**
	 * A path to prepend to any given template paths for all templates.
	 * 
	 * @var string
	 * @since 0.4
	 */
	protected static $prePath = '';
	
	/**
	 * A path/extension to append to any given template paths for all templates.
	 * 
	 * @var string
	 * @since 0.4
	 */
	protected static $postPath = '';
	
	/**
	 * An associative array of variable => 'value' pairs to pass to the template.
	 * 
	 * @var array
	 * @since 0.4
	 */
	protected $register = array();
	
	/**
	 * The path to this Views template.
	 * 
	 * @var string
	 * @since 0.4
	 */
	protected $template = '';
	
	/**
	 * The result of the View's render.
	 * 
	 * @var string
	 * @since 0.4
	 */
	protected $result = '';
	
	/**
	 * Whether or not to return the render result.
	 * 
	 * @var bool
	 * @since 0.4
	 */
	protected $return = false;
	
	/**
	 * Set the prePath.
	 * 
	 * @param string $prePath
	 * @since 0.4
	 */
	public static function setPrePath($prePath) {
		
		static::$prePath = $prePath;
		
	}
	
	/**
	 * Set the postPath.
	 * 
	 * @param string $postPath
	 * @since 0.4
	 */
	public static function setPostPath($postPath) {
		
		static::$postPath = $postPath;
		
	}
	
	/**
	 * Get a new View object for a given template.
	 * 
	 * @param string $template The path to the template to render.
	 * @return void
	 * @since 0.4
	 */
	public function __construct($template) {
		
		$this->template = static::$prePath . $template . static::$postPath;
		
	}
	
	/**
	 * Shortcut to assign template variables.
	 * 
	 * @param string $key   The key being assigned to.
	 * @param mixed  $value The value to assign.
	 * @return void
	 * @since 0.4
	 */
	public function __set($key, $value) {
		
		$this->register[$key] = $value;
		
	}
	
	/**
	 * Getter for {@link $result}.
	 * 
	 * @return string
	 * @since 0.4
	 */
	public function getResult() {
		return $this->result;
	}
	
	/**
	 * Register a variable to pass to the template.
	 * 
	 * @param string $key   The name to use to represent the variable.
	 * @param mixed  $value The value to store.
	 * @return void
	 * @since 0.4
	 */
	public function register($key, $value) {
		
		$this->register[$key] = $value;
		
	}
	
	/**
	 * Render the given template.
	 * 
	 * @param bool $return When true the result is returned instead of echo'd.
	 * @return mixed String if $return = true and the template is available, void otherwise.
	 * @since 0.4
	 */
	public function render($return = false) {
		
		if(!checkTemplate()) return;
		
		$this->return = (bool)$return; // Store $return in an instance variable for improved sandboxing.
		
		// Extract the register into the local scope.
		extract($this->register);
		
		// Begin output buffering.
		ob_start();
		
		// Include the template.
		include $this->template;
		
		// Store the result.
		$this->result = ob_get_clean();
		
		// Return or display it.
		if($this->return) return $this->result;
		echo $this->result;
		
	}
	
	/**
	 * Checks the view's template exists, and throws an exception otherwise.
	 * 
	 * @return bool True if the template exists and is readable, false otherwise.
	 * @throws {@link MissingTemplateException} Thrown when the template cannot be found/read.
	 * @since 0.4
	 */
	public function checkTemplate() {
		
		if(is_readable($this->template)) return true;
		throw new MissingTemplateException($this->template);
		return false;
		
	}
	
}

/**
 * Thrown when {@link View::checkTemplate()} encounters an unreadable template.
 * 
 * @package mvcwebcomponents.exceptions
 * @version 1.0
 */
class MissingTemplateException extends MVCException {
	
	/**
	 * Assign the message based on the missing template.
	 * 
	 * @param string $template The path of the missing template.
	 * @return void
	 * @since 1.0
	 */
	public function __construct($template) {
		
		$this->message = "Can not read template `$template` for view.  Ensure it exists and is readable.";
		
	}
	
}

?>