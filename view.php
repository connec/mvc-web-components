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
 * @version 0.4.2
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
	 * An associative array of variable => 'value' pairs to pass to all templates.
	 * 
	 * @var array
	 * @since 0.4.2
	 */
	protected static $globalRegister = array();
	
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
	 * Set a global value.
	 * 
	 * @param  string $key   The name to use for the data.
	 * @param  mixed  $value The value to store.
	 * @return void
	 */
	public static function registerGlobal($key, $value) {
		
		static::$globalRegister[$key] = $value;
		
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
		$this->checkTemplate();
		
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
	 * Getter for {@link $template}.
	 * 
	 * @return string
	 * @since 0.4.1
	 */
	public function getTemplate() {
		
		return $this->template;
		
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
	 * @return mixed String if $return = true, void otherwise.
	 * @since 0.4
	 */
	public function render($return = false) {
		
		$this->return = (bool)$return; // Store $return in an instance variable for improved sandboxing.
		
		// Extract the registers into the local scope.
		extract(static::$globalRegister);
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
	
	/**
	 * Loads a helper class into the register.
	 * 
	 * This method works best when the helper directory is in the autoload path.
	 * 
	 * @param string $helper The namespaced name of the helper to load.
	 * @return bool True if the given helper could be found and loaded, false otherwise.
	 * @since 0.4.1
	 */
	public function importHelper($helper, $constructOptions = array()) {
		
		if(!class_exists($helper)) {
			throw new MissingHelperException($helper);
			return false;
		}
		
		$class = new $helper($constructOptions);
		$this->helpers[] =& $class;
		
		$denamespaced = @end(explode('\\', $helper));
		$this->{$denamespaced} =& $class;
		
		return true;
		
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

/**
 * Thrown when {@link View::importHelper()} encounters a missing helper.
 * 
 * @package mvcwebcomponents.exceptions
 * @version 1.0
 */
class MissingHelperException extends MVCException {
	
	/**
	 * Assign the message based on the missing helper.
	 * 
	 * @param string $helper The path of the missing helper.
	 * @return void
	 * @since 1.0
	 */
	public function __construct($helper) {
		
		$this->message = "Could not find helper `$helper`.  Ensure it exists and is readable.";
		
	}
	
}

?>