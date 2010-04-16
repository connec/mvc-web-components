<?php

/**
 * Contains the Autoloader class and related exceptions.
 * 
 * @package mvc-web-components.utils
 * @author Chris Connelly
 */
namespace MVCWebComponents;
use MVCWebComponents\Inflector,
	MVCWebComponents\MVCException;

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
 * @version 1.2
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
	 * Sets if Autoloader is 'relaxed'.
	 * 
	 * When relaxed, autoload will not throw an exception for the next autoloaded class.  
	 * Missing dependencies for that class will still raise exceptions.
	 * 
	 * @var bool
	 * @since 1.1
	 */
	protected static $relaxed = false;
	
	/**
	 * An array of included files.
	 * 
	 * @var array
	 * @since 1.2
	 */
	protected static $files = array();
	
	/**
	 * Sets the relaxed flag to true.
	 * 
	 * @return void
	 * @since 1.1
	 */
	public static function relax() {
		
		static::$relaxed = true;
		
	}
	
	/**
	 * Adds the given directory(s) to the search list.
	 * 
	 * @param string $dir A directory to add.
	 * @param string $... Additional directories.
	 * @param bool $checkDir If true, an exception is raised if the dir is not readable.
	 * @return void
	 * @since 1.0
	 */
	public static function addDirectory($dir /* $..., */, $checkDir = true) {
		
		$dirs = func_get_args();
		if(is_bool(end($dirs))) $checkDir = array_pop($dirs);
		foreach($dirs as $key => &$dir) {
			if(!is_dir($dir) or !is_readable($dir)) {
				if($checkDir) throw new MissingDirectoryException($dir);
				unset($dirs[$key]);
				continue;
			}
			$dir = realpath($dir) . DIRECTORY_SEPARATOR;
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
		
		static $relaxed = false;
		if(static::$relaxed) {
			$relaxed = $className;
			static::$relaxed = false;
		}
		
		$fullName = $className;
		$className = @end(explode('\\', $className));
		$namespace = str_replace("\\$className", '', $fullName);
		$file = Inflector::underscore($className) . '.php';
		foreach(self::$directories as $dir) {
			$search = "$dir$file";
			if(file_exists($search) and !in_array($search, static::$files)) {
				require_once $search;
				static::$files[] = $search;
				if(class_exists($fullName, false)) {
					if($relaxed == $fullName) $relaxed = false;
					return;
				}
			}
		}
		
		// If we reach here we haven't found the class, return false.
		if($relaxed == $fullName) {
			$relaxed = false;
			return false;
		}
		throw new MissingClassException($fullName);
		
	}
	
}

/**
 * Register Autoloader::autoload() with PHP.
 */
spl_autoload_register(array('\\MVCWebComponents\\Autoloader', 'autoload'));

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

/**
 * An exception thrown when adding a non-existant directory.
 * 
 * @version 1.0
 * @subpackage exceptions
 */
class MissingDirectoryException extends MVCException {
	
	/**
	 * Set the message based on the missing dir.
	 * 
	 * @param string $dir
	 * @return void
	 * @since 1.0
	 */
	public function __construct($dir) {
		
		$this->message = "No such directory `$dir`; Given to Autoloader::addDirectory().  Ensure it exists and is readable.";
		
	}
	
}

?>