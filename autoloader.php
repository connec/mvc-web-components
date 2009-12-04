<?php

/**
 * Contains the Autoloader class and related exceptions.
 * 
 * @package mvc-web-components.utils
 * @author Chris Connelly
 */
namespace MVCWebComponents;
use MVCWebComponents\Inflector, MVCWebComponents\MVCException;

/**
 * Require the Inflector class and mvc_exception class as it's needed in the autoloader.
 */
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'inflector.php';
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'mvc_exception.php';

/**
 * Autoloader class provides functionality to handle auto loading of classes.
 * 
 * Given an array of directories the autoloader class will attempt to find and load the appropriate file when a non-loaded class is called for.
 * 
 * Also effectively overloads PHP errors with exceptions for missing classes.
 * 
 * @version 1.0
 */
class Autoloader {
	
	/**
	 * An array of directories to search (in decreasing priority).
	 * 
	 * Directories should always ben given relative to the current working directory.
	 * 
	 * @var array
	 * @since 1.0
	 */
	public static $directories = array();
	
	/**
	 * Adds the given directory(s) to the search list.
	 * 
	 * @param string $dir A directory to add.
	 * @param string $... Additional directories.
	 * @return void
	 * @since 1.0
	 */
	public static function addDirectory($dir) {
		
		$dirs = func_get_args();
		foreach($dirs as $key => &$dir) {
			$dir = realpath($dir) . DIRECTORY_SEPARATOR;
			if(!is_dir($dir)) unset($dirs[$key]);
			if(!is_readable($dir)) unset($dirs[$key]);
		}
		self::$directories = array_merge(self::$directories, $dirs);
		
	}
	
	/**
	 * Do the actual autoloading.
	 * 
	 * @param string $className The name of the missing class.
	 * @return void
	 * @since 1.0
	 */
	public static function autoload($className) {
		
		$fullName = $className;
		$className = @end(explode('\\', $className));
		$file = Inflector::underscore($className) . '.php';
		foreach(self::$directories as $dir) {
			$search = "$dir$file";
			if(file_exists($search)) {
				require_once $search;
				if(class_exists($fullName, false)) return;
			}
		}
		
		// If we reach here we haven't found the class, throw an exception.
		throw new MissingClassException($fullName);
		
	}
	
}

/**
 * Register Autoloader::autoload() with PHP.
 */
Autoloader::addDirectory('.');
spl_autoload_register(array('MVCWebComponents\\Autoloader', 'autoload'));

/**
 * An exception thrown when a class cannot be autloaded.
 * 
 * @version 1.0
 */
class MissingClassException extends MVCException {
	
	/**
	 * Sets the message.
	 */
	public function __construct($className) {
		
		$this->message = "Fatal error: could not find class <span style=\"color: #900\">$className</span>.  Search tree:<br/>";
		$this->message .= str_replace(array("\n", ' '), array('<br/>', "&nbsp;"), print_r(Autoloader::$directories, true));
		
	}
	
}

?>