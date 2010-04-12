<?php

/**
 * Contains the Benchmark class.
 * 
 * @package mvc-web-components
 * @author Chris Connelly
 */
namespace MVCWebComponents;
use MVCWebComponents\Set,
	MVCWebComponents\MVCException;

/**
 * Class providing useful benchmarking functionality.
 * 
 * @version 2.0
 */
class Benchmark {
	
	/**
	 * An array of benchmark start times.
	 * 
	 * @var array
	 * @since 2.0
	 */
	protected static $started = array();
	
	/**
	 * An array of benchmark finish times.
	 * 
	 * @var array
	 * @since 2.0
	 */
	protected static $finished = array();
	
	/**
	 * An array of benchmark times.
	 * 
	 * @var array
	 * @since 2.0
	 */
	protected static $benchmarks = array();
	
	/**
	 * Returns whether or not the given benchmark has been started.
	 * 
	 * @param string $key
	 * @return bool
	 * @since 1.0
	 */
	public static function started($key) {
		
		return isset(static::$started[$key]);
		
	}
	
	/**
	 * Returns whether or not the given benchmark has ended.
	 * 
	 * @param string $key
	 * @return bool
	 * @since 1.0
	 */
	public static function finished($key) {
		
		return isset(static::$benchmarks[$key]);
		
	}
	
	/**
	 * Starts a benchmark.
	 * 
	 * @param string $key
	 * @param float  $time  The time to set as the benchmark's start.  microtime used if not set.
	 * @param bool   $force When true the benchmark is started regardless of whether or not it has already been started.
	 * @return void
	 * @since 1.0
	 * @throws MVCException Thrown when the benchmark has already started (and $forced is false).
	 */
	public static function start($key, $time = 0, $force = false) {
		
		if(!$force and static::started($key))
			throw new MVCException("Cannot start running benchmark `$key`.");
		if(!$time) $time = microtime(true);
		static::$started[$key] = $time;
		
	}
	
	/**
	 * Ends a benchmark.
	 * 
	 * @param string $key
	 * @return float The time of the benchmark.
	 * @since 1.0
	 */
	public static function end($key) {
		
		if(!static::started($key))
			throw new MVCException("Cannot end unstarted benchmark `$key`.");
		static::$finished[$key] = microtime(true);
		return static::$benchmarks[$key] = static::$finished[$key] - static::$started[$key];
		
	}
	
	/**
	 * Return the result of the given benchmark.
	 * 
	 * @param string $key
	 * @return mixed The value stored in $key (or {$key}_total).
	 * @since 1.0
	 */
	public static function read($key) {
		
		if(static::finished($key)) return static::$benchmarks[$key];
		else throw new MVCException("Cannot read unfinished benchmark `$key`.");
		
	}
	
	/**
	 * Resets a benchmark.
	 * 
	 * @param string $key
	 * @return void
	 * @since 1.0
	 */
	public static function reset($key) {
		
		if(static::started($key)) unset(static::$started[$key]);
		if(static::finished($key)) {
			unset(static::$finished[$key]);
			unset(static::$benchmarks[$key]);
		}
		
	}
	
	/**
	 * Combines the given benchmarks.
	 * 
	 * @param string $name       The name to give the resulting benchmark.
	 * @param mixed  $benchmarks Either an array of benchmark keys to combine 
	 *               OR a string, in which case keys beginning with $benchmark will be combined.
	 * @return float
	 * @since 2.0
	 */
	public static function combine($name, $benchmarks) {
		
		$total = 0;
		if(is_array($benchmarks)) {
			foreach($benchmarks as $key) {
				if(static::finished($key)) $total += static::$benchmarks[$key];
				else throw new MVCException("Cannot combine unfinished benchmark `$key`.");
			}
		} else {
			foreach(static::$benchmarks as $key => $time)
				if(strpos($key, $benchmarks) === 0) $total += $time;
		}
		return static::$benchmarks[$name] = $total;
		
	}
	
	/**
	 * Returns an array of 'name' => time pairs for all finished benchmarks.
	 * 
	 * @return array
	 * @since 1.0
	 */
	public static function summary() {
		
		return static::$benchmarks;
		
	}
	
}

?>