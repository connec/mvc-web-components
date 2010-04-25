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
		static $validUrlPattern = '|^(?:/[a-z0-9_*:]+)*/$|i';
		if(!preg_match($validUrlPattern, $urlPattern)) throw new InvalidUrlPatternException($urlPattern);
		
		// Build the regex pattern from the url pattern.
		$regex = '|^' . str_replace('*', '(?<other>.*)', $urlPattern) . '$|i';
		if(strpos($urlPattern, ':') !== false)
			// We have variables...
			$regex = preg_replace('|:([a-z]+)|i', '(?<$1>[^/]+)', $regex);
		
		// Register it.
		Router::$connections[] = array('urlPattern' => $urlPattern, 'regex' => $regex, 'parameters' => $parameters);
		
	}
	
	/**
	 * Matches a URL to a connection.
	 *
	 * Searches registered connections for a URL pattern matching $url and returns the associated parameters.
	 *
	 * @param string $url    The URL to route.
	 * @param bool   $error  When set to true, an exception if thrown when no matching connection is found.
	 * @param array  $ignore An array of paremeters to be ignored.  Same format as $connection.
	 * @return mixed False if no connections matched or the connections parameters if a match is found.
	 * @since 1.0
	 */
	public static function route($url, $error = true, $ignore = array()) {
		
		// Reset the current connection.
		static::$connection = null;
		
		// Find the query string.
		if(($start = strpos($url, '?')) !== false) {
			$queryStr = substr($url, $start + 1);
			$url = substr($url, 0, $start);
		} else
			$queryStr = isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : '';
		
		// Append a trailing slash if there isn't one.
		if(substr($url, -1) != '/') $url .= '/';
		
		// Find a matching connection.
		foreach(static::$connections as $connection) {
			if(in_array($connection, $ignore)) continue;
			
			// Check for a match.
			if(preg_match($connection['regex'], $url, $matches)) {
				static::$connection = $connection; // Store the matching connection for reference.
				array_shift($matches); // Ignore the total match.
				
				// Append any wilcard matches / query string variables.
				if(isset($matches['other'])) {
					$connection['parameters'] =
						array_merge($connection['parameters'], explode('/', $matches['other']));
					unset($matches['other']);
				}
				
				// Sort the parameters.
				$other = array();
				foreach($connection['parameters'] as $key => &$value) {
					foreach(array_keys($matches) as $var) {
						if(is_int($var)) continue;
						if(strpos($value, ":$var") !== false) {
							$value = str_replace(":$var", $matches[$var], $value);
							break;
						}
					}
					if(!is_string($key)) {
						$other[] = $value;
						unset($connection['parameters'][$key]);
					}
				}
				$connection['parameters']['other'] = $other;
				
				// Add any query string variables to 'other'
				parse_str($queryStr, $other);
				$connection['parameters']['other'] =
					array_merge($connection['parameters']['other'], $other);
				
				// Infer the paremeters from the matches if no paremeters are given.
				if(count($connection['parameters']) === 1) {
					foreach($matches as $var => $val) {
						if(is_int($var)) continue;
						$connection['parameters'][$var] = $val;
					}
				}
				
				return $connection['parameters'];
			}
		}
		
		if($error) throw new NoConnectionException($url);
		else return false;
		
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