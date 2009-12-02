<?php

/**
 * Contains the View class.
 * 
 * @package utilities
 * @author Chris Connelly
 */
namespace MVCWebComponents;
use MVCWebComponents\MVCException;

/**
 * Generates a HTML page based on page templates, templated elements and assigned variables.
 * 
 * @version 0.3
 */
Class View {
	
	/**
	 * A static register of views.
	 * 
	 * Format:
	 * <code>
	 * $views = array('ViewAlias' => View Instance);
	 * </code>
	 * 
	 * @var array
	 * @since 0.2
	 */
	private static $views = array();
	
	/**
	 * A static register of elements.
	 *
	 * Format:
	 * <code>
	 * $elements = array('ElementAlias' => Element Instance);
	 * </code>
	 *
	 * @var array
	 * @since 0.2
	 */
	private static $elements = array();
	
	/**
	 * The alias under which the view is stored.
	 * 
	 * @var string
	 * @since 0.2
	 */
	public $alias;
	
	/**
	 * The path to the template that the view instance will render.
	 * 
	 * @var string
	 * @since 0.2
	 */
	public $template;
	
	/**
	 * Contains a register of all assigned template variables.
	 * 
	 * Format:
	 * <code>
	 * $register = array('templateVariableName' => "templateVariableName's Value");
	 * </code>
	 *
	 * @var array
	 * @since 0.2
	 */
	private $register = array();
	
	/**
	 * Whether or not to return the result of the template instead of displaying it.
	 *
	 * @var bool
	 * @since 0.2
	 */
	public $return = false;
	
	/**
	 * Stores the result of the template.
	 *
	 * @var string
	 * @since 0.2
	 */
	public $result;
	
	/**
	 * Overloads variable setting.
	 * 
	 * Registers variables assigned to the view as template variables unless they are predefined by the class.
	 *
	 * @param string $var The name of the variable being assigned.
	 * @param string $value The value being assigned.
	 * @return void
	 * @since 0.2
	 */
	public function __set($var, $value) {
		
		if(!in_array($var, get_class_vars('View'))) {
			$this->register[$var] = $value;
		}else {
			$this->{$var} = $value;
		}
		
	}
	
	/**
	 * Allows returning of template variables assigned to {@link $register}.
	 *
	 * @param string $var The name of the variable to return.
	 * @return mixed The value of $var.
	 * @since 0.2
	 */
	public function __get($var) {
		
		if(!in_array($var, get_class_vars('View'))) {
			return $this->register[$var];
		}else {
			return $this->{$var};
		}
		
	}
	
	/**
	 * Get an instance of a view.
	 * 
	 * Searches the {@link $views} variable for the view referenced by $viewAlias and returns it if it exists, or creates and returns it if not.
	 * 
	 * @param string $viewAlias The alias (or path) of the view to return.
	 * @return object An instance of the view referenced by $viewAlias.
	 * @since 0.2
	 */
	public static function &getView($viewAlias) {
		
		if(isset(View::$views[$viewAlias])) {
			return View::$views[$viewAlias];
		}else {
			View::$views[$viewAlias] = new View($viewAlias);
			return View::$views[$viewAlias];
		}
		
	}
	
	/**
	 * Get an instance of an element.
	 *
	 * Searches the {@link $elements} variable for the element referenced by $elementAlias and returns it if it exists, or creates and returns it if not.
	 *
	 * @param string $elementAlias The alias (or path) of the element to return.
	 * @return object An instance of the element referred to by $elementAlias.
	 * @since 0.2
	 */
	public static function &getElement($elementAlias) {
		
		if(isset(View::$elements[$elementAlias])) {
			return View::$elements[$elementAlias];
		}else {
			View::$elements[$elementAlias] = new View($elementAlias);
			View::$elements[$elementAlias]->return = true;
			return View::$elements[$elementAlias];
		}
		
	}
	
	/**
	 * Assigns an alias and default view path to the newly created view.
	 * 
	 * @param string $viewAlias The alias and default view path for the view.
	 * @return void
	 * @since 0.2
	 */
	public function __construct($viewAlias) {
		
		// Assign default values for alias and template.
		$this->alias = $viewAlias;
		$this->template = $viewAlias . '.tpl';
		
	}
	
	/**
	 * Renders the view to the browser, unless {@link $return} is set to true.
	 *
	 * @return mixed Void, unless {@link $return} is true, in which case returns the rendered page.
	 * @since 0.2
	 */
	public function render() {
		
		// Check the template exists.
		if(!$this->templateExists()) {
			$this->result = "Template '$this->template' not found.";
			throw new MissingTemplateException($this->template);
		}else {
			// Load the registered template variables into the local scope.
			extract($this->register);
			
			// Start output buffering.
			ob_start();
			
			// Load the template.
			include $this->template;
			
			// Get the result of the template from the output buffer.
			$this->result = ob_get_clean();
		}
		
		if($this->return) return $this->result;
		else echo $this->result;
		
	}
	
	/**
	 * Checks if {@link $template} exists, returns true if so and false otherwise.
	 *
	 * @return bool True if {@link $template} exists, false otherwise.
	 * @since 0.2
	 */
	public function templateExists() {
		
		if(file_exists($this->template)) return true;
		else return false;
		
	}
	
	/**
	 * Links a helper to the view for use by templates.
	 *
	 * @param string $helper The path to the helper.
	 * @return bool True if the helper was found and loaded false otherwise.
	 * @since 0.2
	 */
	public function useHelper($helper) {
		
		$helper = "$helper.php";
		if(!file_exists($helper)) return false;
		
		$helperName = Inflector::camelize(basename($helper, '.php'));
		
		// Include the $helper file.
		include_once $helper;
		
		// Check the class was loaded.
		if(!class_exists($helperName)) return false;
		
		// Register it with the... register.
		$this->{$helperName} = new $helperName;
		
		return true;
		
	}
	
}

/**
 * Missing template exception.
 *
 * An exception thrown when View->render() encounters a missing template.
 *
 * @subpackage exceptions
 * @version 1.0
 */

Class MissingTemplateException extends MVCException {
	
	/**
	 * Assigns the message to the exception.
	 *
	 * @param string $template The missing template.
	 * @return void
	 * @since 1.0
	 */
	public function MissingTemplateException($template) {
		
		$this->message = "Could not find template '$template'.";
		
	}
	
}

?>