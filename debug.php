<?php

/**
 * Contains Debug class.
 * 
 * @package utilities
 * @author Chris Connelly
 */
namespace MVCWebComponents;

/**
 * Provides useful debugging functions.
 * 
 * @version 1.1
 */
class Debug {
	
	/**
	 * A hash of watched variable references.
	 * 
	 * @var array
	 * @since 1.0
	 */
	protected static $watched = array();
	
	/**
	 * A hash of css classes to use in the markup.
	 * 
	 * @var array
	 * @since 1.1
	 */
	public static $elementClasses = array(
		'header' => 'debug-header',
		'table' => 'debug-table',
		'tableHeading' => 'debug-table-heading',
		'tableRowEven' => 'debug-table-row-even',
		'tableRowOdd' => 'debug-table-row-odd',
		'dump' => 'debug-dump',
		'traceRowEven' => 'debug-trace-row-even',
		'traceRowOdd' => 'debug-trace-row-odd'
	);
	
	/**
	 * Store a reference to a variable to watch.
	 * 
	 * @param string $key The name to store the reference under.
	 * @param mixed $var The variable (by reference) to watch.
	 * @return void
	 * @since 1.0
	 */
	public static function watch($key, &$var) {
		
		if(!isset(self::$watched[$key])) self::$watched[$key] = array();
		self::$watched[$key]['ref'] =& $var;
		
	}
	
	/**
	 * Return the value of the reference indexed by $key.
	 * 
	 * @param string $key The key of the reference to return.
	 * @return mixed The value of the reference.
	 * @since 1.0
	 */
	public static function watchValue($key, $bookmark = '') {
		
		if(is_object($key)) return clone self::$watched[$key]['ref'];
		else return self::$watched[$key]['ref'];
		
	}
	
	/**
	 * Bookmark a variable value.
	 * 
	 * Bookmarks the current value of the reference indexed by $key with bookmark $bookmark.
	 * 
	 * @param string $key The key in the {@link $watched} array to bookmark.
	 * @param string $bookmark A string key to bookmark the value under.
	 * @since 1.0
	 */
	public static function bookmark($key, $bookmark) {
		
		if(!isset(self::$watched[$key]['bookmarks'])) self::$watched[$key]['bookmarks'] = array();
		self::$watched[$key]['bookmarks'][$bookmark] = self::watchValue($key);
		
	}
	
	/**
	 * Return the value of bookmark $bookmark in $key.
	 * 
	 * @param string $key The key of the bookmarked variable.
	 * @param string $bookmark The name of the bookmark to return.
	 * @return mixed The value of the bookmark of $key named $bookmark.
	 * @since 1.0
	 */
	public static function bookmarkValue($key, $bookmark) {
		
		if(is_object(self::$watched[$key]['bookmarks'][$bookmark])) return clone self::$watched[$key]['bookmarks'][$bookmark];
		else return self::$watched[$key]['bookmarks'][$bookmark];
		
	}
	
	/**
	 * Outputs or returns a table from a key => value hash.
	 * 
	 * @param array $data The data from which to make a table.
	 * @param bool $return When true returns the output instead of displaying it.
	 * @return void
	 * @since 1.0
	 */
	public static function table($data, $return) {
		
		$output = '<table class="' . self::$elementClasses['table'] . '">';
		$output .= '<tr class="' . self::$elementClasses['tableHeading'] . '"><th>Key</th><th>Value</th></tr>';
		foreach($data as $key => $value) {
			if(isset($type) and $type == 'Even') $type = 'Odd';
			else $type = 'Even';
			
			ob_start();
			var_dump($value);
			$dump = ob_get_clean();
			
			$output .= '<tr class="' . self::$elementClasses["tableRow$type"] . "\"><td>$key</td><td><pre>$dump</pre></td></tr>";
		}
		$output .= '</table>';
		
		if($return) return $output;
		else echo $output;
		
	}
	
	/**
	 * Prints or returns a table of watched variable values.
	 * 
	 * @param bool $return If true, returns the output instead of printing it.
	 * @since 1.0
	 */
	public static function watchTable($return = false) {
		
		$output = '<h2 class="' . self::$elementClasses['header'] . '">Watched Variables.</h2>';
		$array = array();
		foreach(self::$watched as $key => &$value) {
			$array[$key] = $value['ref'];
		}
		$output .= self::table($array, true);
		
		if($return) return $output;
		else echo $output;
		
	}
	
	/**
	 * Prints or returns a table of bookmarks of $key.
	 * 
	 * @param string $key The key of the bookmarks to display.
	 * @param bool $return If true returns the output HTML instead of echoing it.
	 * @return mixed Void if $return is false, a string otherwise.
	 * @since 1.0
	 */
	public static function bookmarkTable($key, $return = false) {
		
		$output ='<h2 class="' . self::$elementClasses['header'] . "\">Bookmarks ($key)</h2>";
		$output .= self::table(self::$watched[$key]['bookmarks'], true);
		
		if($return) return $output;
		else echo $output;
		
	}
	
	/**
	 * Returns a formatted string backtrace.
	 * 
	 * @param bool $return When true output is returned, not displayed.
	 * @return mixed A pretty formatted backtrace if $return is true, void otherwise.
	 * @since 1.0
	 */
	public static function backtrace($return = false) {
		
		$output = '';
		$backtrace = debug_backtrace(false);
		$i = count($backtrace);
		foreach($backtrace as $trace) {
			@$output .= '<span class="' . self::$elementClasses['traceRow' . ($i % 2 == 0 ? 'Even' : 'Odd')] . '">' . "$i: {$trace['class']}{$trace['type']}{$trace['function']}() <i>{$trace['file']} line {$trace['line']}</i></span>\n";
			$i -= 1;
		}
		
		if($return) return $output;
		else echo $output;
		
	}
	
	/**
	 * Prints a given value.
	 * 
	 * Prints a value using var_dump and appends a backtrace.
	 * 
	 * @param mixed $value The value to dump.
	 * @return void
	 * @since 1.0
	 */
	public static function dump($value) {
		
		$output = "\n<pre class=\"" . self::$elementClasses['dump'] . "\">\n<h2 class=\"" . self::$elementClasses['header'] . "\">Debug Output</h2>\n";
		ob_start();
		var_dump($value);
		$output .= ob_get_clean();
		$output .= Debug::backtrace(true);
		
		echo $output;
		
	}
	
}

?>