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
 * @version 1.3
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
	 * Store a reference to a variable to watch.
	 * 
	 * @param string $key The name to store the reference under.
	 * @param mixed $var The variable (by reference) to watch.
	 * @return void
	 * @since 1.0
	 */
	public static function watch($key, &$var) {
		
		if(!isset(static::$watched[$key])) static::$watched[$key] = array();
		static::$watched[$key]['ref'] =& $var;
		
	}
	
	/**
	 * Return the value of the reference indexed by $key.
	 * 
	 * @param string $key The key of the reference to return.
	 * @return mixed The value of the reference.
	 * @since 1.0
	 */
	public static function watchValue($key, $bookmark = '') {
		
		if(is_object($key) or is_array($key)) return unserialize(serialize(static::$watched[$key]['ref']));
		else return static::$watched[$key]['ref'];
		
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
		
		if(!isset(static::$watched[$key]['bookmarks'])) static::$watched[$key]['bookmarks'] = array();
		static::$watched[$key]['bookmarks'][$bookmark] = static::watchValue($key);
		
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
		
		if(is_object(static::$watched[$key]['bookmarks'][$bookmark])) return clone static::$watched[$key]['bookmarks'][$bookmark];
		else return static::$watched[$key]['bookmarks'][$bookmark];
		
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
		
		$output = '<table class="debug-table">';
		$output .= '<tr class="debug-table-heading"><th>Key</th><th>Value</th></tr>';
		foreach($data as $key => $value) {
			if(isset($type) and $type == 'even') $type = 'odd';
			else $type = 'even';
			
			ob_start();
			var_dump($value);
			$dump = ob_get_clean();
			
			$output .= "<tr class=\"$type\"><td>$key</td><td><pre>$dump</pre></td></tr>";
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
		
		$output = '<h2>Watched Variables</h2>';
		$array = array();
		foreach(static::$watched as $key => &$value) {
			$array[$key] = $value['ref'];
		}
		$output .= static::table($array, true);
		
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
		
		$output ="<h2>Bookmarks ($key)</h2>";
		$output .= static::table(static::$watched[$key]['bookmarks'], true);
		
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
		
		$trace = debug_backtrace(false);
		array_shift($trace);
		$output = static::formatTrace($trace);
		if($return) return $output;
		else echo $output;
		
	}
	
	/**
	 * Formats a backtrace into a neat HTML string.
	 * 
	 * @param array $trace
	 * @return string
	 * @since 1.3
	 */
	public static function formatTrace($trace) {
		
		$default_trace = array(
			'function' => 'Unknown',
			'line' => 'Unknown',
			'file' => 'Unknown',
			'class' => '',
			'object' => null,
			'type' => '',
			'args' => array()
		);
		
		$output = '<pre class="trace">';
		$i = count($trace);
		
		foreach($trace as $line) {
			$line = array_merge($default_trace, $line);
			$output .= '<div class="' . ($i % 2 == 0 ? 'even' : 'odd') . '">' . "$i: {$line['class']}{$line['type']}{$line['function']}()<br/>";
			$output .= "<span class=\"small\">[{$line['file']}:{$line['line']}]</span></div>";
			$i --;
		}
		$output .= '</pre>';
		
		return $output;
	}
	
	/**
	 * Extends PHP's var_dump with an optional return parameter.
	 * 
	 * @param mixed $var
	 * @param bool  $return
	 * @param bool  $trace  When true, appends a backtrace to the output.
	 * @return mixed
	 */
	public static function var_dump($var, $return = false, $trace = true) {
		
		ob_start();
		var_dump($var);
		
		$output = '<pre class="debug-dump">';
		$output .= ob_get_clean();
		if($trace) $output .= Debug::backtrace(true);
		$output .= '</pre>';
		
		if($return) return $output;
		echo $output;
		
	}
	
	/**
	 * Returns a summary of watched variables.
	 * 
	 * @return array
	 * @since 1.4
	 */
	public static function summary() {
		
		return static::$watched;
		
	}
	
}

?>