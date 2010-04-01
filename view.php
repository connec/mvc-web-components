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
 * @version 0.4.4
 */
class View
{
	
	/**
	 * An array of possible paths to prepend to any given template paths for all templates.
	 * 
	 * @var string
	 * @since 0.4.3
	 */
	protected static $prePaths = array();
	
	/**
	 * An array of possible paths/extensions to append to any given template paths for all templates.
	 * 
	 * @var string
	 * @since 0.4.3
	 */
	protected static $postPaths = array();
	
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
	 * Adds an arbitrary number of pre-paths to the array of pre-paths.
	 * 
	 * @param string $path
	 * @return void
	 * @since 0.4.3
	 */
	public static function addPrePath($path) {
		
		static::$prePaths = array_merge(func_get_args(), static::$prePaths);
		
	}
	
	/**
	 * Adds an arbitrary number of post-paths to the postPaths array.
	 * 
	 * @param string $path
	 * @return void
	 * @since 0.4.3
	 */
	public static function addPostPath($path) {
		
		static::$postPaths = array_merge(func_get_args(), static::$postPaths);
		
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
	 * Helper to create a view, assign some variables and return the result.
	 * 
	 * @param string $template The partial template to use.
	 * @param array  $vars     An array of 'var' => value pairs
	 * @return string The result of the partial
	 * @since 0.4.4
	 */
	public static function partial($template, $vars = array()) {
		
		$partial = new View($template);
		foreach($vars as $var => $value) $partial->register($var, $value);
		return $partial->render(true);
		
	}
	
	/**
	 * Get a new View object for a given template.
	 * 
	 * @param string $template The path to the template to render.
	 * @return void
	 * @since 0.4
	 */
	public function __construct($template) {
		
		// Replace forward-slashes by the system's directory separator.
		$template = str_replace('/', DIRECTORY_SEPARATOR, $template);
		
		// Find a suitable pre/post path combination.
		$tried = array();
		foreach(static::$prePaths as $prePath) {
			foreach(static::$postPaths as $postPath) {
				$this->template = "$prePath$template$postPath";
				if($this->checkTemplate(false)) return;
				$tried[] = $this->template;
			}
		}
		throw new MissingTemplateException($template, $tried);
		
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
	 * @param bool $error When true, throws an exception.
	 * @return bool True if the template exists and is readable, false otherwise.
	 * @throws {@link MissingTemplateException} Thrown when the template cannot be found/read.
	 * @since 0.4
	 */
	public function checkTemplate($error = true) {
		
		if(is_readable($this->template)) return true;
		if($error) throw new MissingTemplateException($this->template);
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
	public function __construct($template, $tried = array()) {
		
		$this->message = "Can not read template `$template` for view.  ";
		if(!empty($tried)) $this->message .= 'Tried:' . print_r($tried, true);
		$this->message .= "Ensure it exists and is readable.";
		
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