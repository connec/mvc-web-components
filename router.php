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
 * Principle: 'connect' a url pattern to an array of parameters.  Url pattern 
 * can contain variables and wildcards which can be passed to the parameters.
 *
 * E.g.
 * <code>
 *    Router::connect('/:controller/:action/*',
 *       array('controller' => ':controller', 'action' => ':action'));
 *    $params = Router::route('/pages/view/home');
 * </code>
 * This will give $params the value:
 * <code>
 * array(
 *    'controller' => 'pages',
 *    'action'     => 'view',
 *    'other'      => array('home')
 * );
 * </code>
 *
 * 
 * @version 1.3
 */
class Router {
	
	/**
	 * A static register of matchable patterns, or 'connections'.
	 *
	 * Format:
	 * <code>
	 * static $connections = array(
	 *    'urlPattern' => '/connection/:syntax/pattern/*',
	 *    'regex' => '/connection/(?<syntax>[^/]+)/pattern/(?<other>.*)',
	 *    'parameters' => array('var1' => 'value', 'var2' => 'value')
	 * );
	 *
	 * @var array
	 * @since 1.0
	 */
	protected static $connections = array();
	
	/**
	 * The connection that matched for the last call to {@link route()}.
	 * 
	 * @var array
	 * @since 1.3
	 */
	public static $connection = null;
	
	/**
	 * Clears all registered connections.  Useful for testing.
	 * 
	 * @return void
	 * @since 1.2
	 */
	public static function disconnectAll() {
		
		static::$connections = array();
		
	}
	
	/**
	 * Registers a connection of a url pattern to a set of variables.
	 *
	 * @param string $urlPattern A pattern to be matched against a url, uses 'connection syntax'.
	 * @param array $variables An array of variables to be returned if a url matches $urlPattern, can contain 'connection variables'.
	 * @return bool True if $urlPattern is valid, false otherwise.
	 * @since 1.0
	 */
	public static function connect($urlPattern, $parameters = array()) {
		
		// Handle multiple urlPatterns.
		if(is_array($urlPattern)) {
			foreach($urlPattern as $p) static::connect($p, $parameters);
			return;
		}
		
		// Append the trailing "/" if one is missing.
		if(substr($urlPattern, -1) != '/') $urlPattern .= '/';
		
		// Check $urlPattern uses valid 'connection syntax'.
		$validUrlPattern = '#^(?:/[a-z0-9_*:]+)*/$#i';
		if(!preg_match($validUrlPattern, $urlPattern)) throw new InvalidUrlPatternException($urlPattern);
		
		// Build the regex pattern from the url pattern.
		// At it's simplest, the url pattern is the regex... with wildcard '*' replaced with '.*'.
		$regex = '|^' . str_replace('*', '(?<other>.*)', $urlPattern) . '$|i';
		if(strpos($urlPattern, ':') !== false) {
			// We have variables...
			$find = '|:([a-z]+)|i';
			$replace = '(?<$1>[^/]+)';
			$regex = preg_replace($find, $replace, $regex);
		}
		
		// Collect any parameters without keys under the 'other' key.
		if(!isset($parameters['other'])) $parameters['other'] = array();
		foreach($parameters as $key => $val) {
			if(is_string($key)) continue;
			$parameters['other'][] = $val;
			unset($parameters[$key]);
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
	 * @param bool   $error When set to true, an exception if thrown when no matching connection is found.
	 * @return mixed False if no connections matched or the connections parameters if a match is found.
	 * @since 1.0
	 */
	public static function route($url, $error = true) {
		
		static::$connection = null;
		
		// Append the trailing '/' if it's missing.
		if(substr($url, -1) != '/') $url .= '/';
		
		// Checks each rule in order and returns the parameters of the first matched rule.
		foreach(Router::$connections as $connection) {
			$matches = array();
			if(preg_match($connection['regex'], $url, $matches)) {
				static::$connection = $connection;
				
				array_shift($matches); // Ignore the 'overall' match
				
				// If parameters is just 'other' (required), infer the rest from any variables.
				if(count($connection['parameters']) === 1) {
					foreach($matches as $var => $val) {
						if(is_int($var) or $var == 'other') continue;
						$connection['parameters'][$var] = $val;
					}
				}
				
				// Check if any parameters use connection variables.
				foreach($connection['parameters'] as &$parameter) {
					if(is_array($parameter) or strpos($parameter, ':') === false) continue;
					
					// Replace the :connectionVariable with the corresponding value in $matches.
					$variable = substr($parameter, strpos($parameter, ':') + 1);
					$parameter = str_replace(":$variable", $matches[$variable], $parameter);
				}
				
				// Process 'other' parameters if they exist.
				if(isset($matches['other'])) {
					foreach(explode('/', $matches['other']) as $other) {
						if(strpos($other, ':') === false) $connection['parameters']['other'][] = $other;
						else {
							list($var, $val) = explode(':', $other);
							$connection['parameters']['other'][$var] = $val;
						}
					}
				}
				
				// Return the parameters.
				return $connection['parameters'];
			}
		}
		
		// No match was found, throw an exception or return false.
		if($error) throw new NoConnectionException($url);
		return false;
		
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
class InvalidUrlPatternException extends MVCException {
	
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
 * No connection exception.
 * 
 * An exception thrown when {@link Router::route()} cannot match the given url to a connection.
 * 
 * @subpackage exceptions
 * @version 1.0
 */
class NoConnectionException extends MVCException {
	
	/**
	 * Sets the message.
	 * 
	 * @param string $url The URL that could not be matched.
	 * @return void
	 * @since 1.0
	 */
	public function __construct($url) {
		
		$this->message = "No valid connection for url `$url`.";
		
	}
	
}

?>