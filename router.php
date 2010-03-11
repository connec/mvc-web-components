<?php

/**
 * Contains the Router class and related exceptions.
 * 
 * @package utilities
 * @author Chris Connelly
 */
namespace MVCWebComponents;
use MVCWebComponents\MVCException;

/**
 * Gets an array of parameters from an input URL using defined patterns.
 * 
 * @version 1.1
 */
Class Router {
	
	/**
	 * A static register of matchable patterns, or 'connections'.
	 *
	 * Format:
	 * <code>
	 * static $connections = array(
	 *    'urlPattern' =? '/connection/:syntax/pattern/*',
	 *    'regex' => '/connection/(?<syntax>[^/]+)/pattern/(?<other>.*)',
	 *    'parameters' => array('var1' => 'value', 'var2' => 'value')
	 * );
	 *
	 * @var array
	 * @since 1.0
	 */
	public static $connections = array();
	
	/**
	 * Registers a connection of a url pattern to a set of variables.
	 *
	 * @param string $urlPattern A pattern to be matched against a url, uses 'connection syntax'.
	 * @param array $variables An array of variables to be returned if a url matches $urlPattern, can contain 'connection variables'.
	 * @return bool True if $urlPattern is valid, false otherwise.
	 * @since 1.0
	 */
	public static function connect($urlPattern, $parameters) {
		
		// Handle multiple urlPatterns.
		if(is_array($urlPattern)) {
			foreach($urlPattern as $p) static::connect($p, $parameters);
			return;
		}
		
		// Check $urlPattern uses valid 'connection syntax'.
		$validUrlPattern = '#(?:/[a-z0-9_*:]+)+|/#i';
		if(!preg_match($validUrlPattern, $urlPattern)) throw new InvalidUrlPatternException($urlPattern);
		
		// Build the regex pattern from the url pattern.
		// At it's simplest, the url pattern is the regex... with wildcard '*' replaced with '.*'.
		$regex = '|^' . str_replace('*', '(?<other>.*)', $urlPattern) . '$|i';
		if(strpos($urlPattern, ':') !== false) {
			// We have variables...
			$find = '|:([a-z]+)|i';
			$replace = "(?<$1>[^/]+)";
			$regex = preg_replace($find, $replace, $regex);
		}
		
		// Register it.
		Router::$connections[] = array('urlPattern' => $urlPattern, 'regex' => $regex, 'parameters' => $parameters);
		
	}
	
	/**
	 * Matches a URL to a connection.
	 *
	 * Searches registered connections for a URL pattern matching $url and returns the associated parameters.
	 *
	 * @param string $url The URL to route.
	 * @return mixed False if no connections matched or the connections parameters if a match is found.
	 * @since 1.0
	 */
	public static function route($url) {
		
		// Checks each rule in order and returns the parameters of the first matched rule.
		foreach(Router::$connections as $connection) {
			$matches = array();
			if(preg_match($connection['regex'], $url, $matches)) {
				array_shift($matches);
				
				// Check if any parameters use connection variables.
				foreach($connection['parameters'] as &$parameter) {
					if(is_array($parameter) or strpos($parameter, ':') === false) continue;
					
					// Replace the :connectionVariable with the corresponding value in $matches.
					$variable = substr($parameter, strpos($parameter, ':') + 1);
					$parameter = str_replace(":$variable", $matches[$variable], $parameter);
				}
				
				// Process 'other' parameters if they exist.
				if(isset($matches['other'])) $connection['parameters']['other'] = explode('/', $matches['other']);
				
				// Return the parameters.
				return $connection['parameters'];
			}
		}
		
		// No match was found, return false.
		return false;
		
	}
	
	/**
	 * Load connections from a file.
	 *
	 * Basically just includes the file, which is assumed to contain only Router::connect() method calls.
	 *
	 * @param string $file The file to load.
	 * @return bool True if the file is succesfully loaded, false otherwise.
	 * @since 1.0
	 * @todo Implement support for XML formatted connection files.
	 */
	public static function loadConnections($file) {
		
		if(!file_exists($file)) throw new MissingConnectionFileException($file);
		
		include $file;
		
		return true;
		
	}
	
}

/**
 * Invalid URL pattern exception.
 *
 * An exception thrown when {@link Router::connect()} encounters an invalid URL pattern.
 *
 * @subpackage exceptions
 * @version 1.0
 */
Class InvalidUrlPatternException extends MVCException {
	
	/**
	 * Sets the message.
	 *
	 * @param string $urlPattern The invalid url pattern.
	 * @return void
	 * @since 1.0
	 */
	public function __construct($urlPattern) {
		
		$this->message = "Invalid URL pattern '$urlPattern' given to Router::connect().";
		
	}
	
}

/**
 * Invalid connections file exception.
 *
 * An exception thrown when {@link Router::loadConnections()} encounters an invalid connection file.
 *
 * @subpackage exceptions
 * @version 1.0
 */
Class MissingConnectionFileException extends MVCException {
	
	/**
	 * Sets the message.
	 *
	 * @param string $file The invalid connection file.
	 * @return void
	 * @since 1.0
	 */
	public function __construct($file) {
		
		$this->message = "Invalid connection file '$file', check it exists.";
		
	}
	
}

?>