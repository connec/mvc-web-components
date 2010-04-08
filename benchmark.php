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
 * @version 1.0
 */
class Benchmark extends Set {
	
	/**
	 * Returns whether or not the given benchmark has been started.
	 * 
	 * @param string $key
	 * @return bool
	 * @since 1.0
	 */
	public static function started($key) {
		
		return static::check($key . '_start');
		
	}
	
	/**
	 * Returns whether or not the given banchmark has ended.
	 * 
	 * @param string $key
	 * @return bool
	 * @since 1.0
	 */
	public static function ended($key) {
		
		return static::check($key . '_end') and static::check($key . '_total');
		
	}
	
	/**
	 * Starts a benchmark.
	 * 
	 * @param string $key
	 * @param bool $force When true the benchmark is started regardless of whether or not it has already been started.
	 * @return void
	 * @since 1.0
	 * @throws MVCException Thrown when the benchmark has already started (and $forced is false).
	 */
	public static function start($key, $force = false) {
		
		if(!$force and static::started($key))
			throw new MVCException("Cannot start running benchmark `$key`.");
		static::write($key . '_start', microtime(true));
		
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
		static::write($key . '_end', microtime(true));
		static::write($key . '_total', static::read($key . '_end') - static::read($key . '_start'));
		return static::read($key . '_total');
		
	}
	
	/**
	 * Return the value of the given key.
	 * 
	 * Adds '_total' to the given key for convenience (when applicable).
	 * 
	 * @param string $key
	 * @return mixed The value stored in $key (or {$key}_total).
	 * @since 1.0
	 */
	public static function read($key) {
		
		if(static::check($key . '_total')) return static::read($key . '_total');
		else return parent::read($key);
		
	}
	
	/**
	 * Resets a benchmark.
	 * 
	 * @param string $key
	 * @return void
	 * @since 1.0
	 */
	public static function reset($key) {
		
		static::clear($key . '_start');
		static::clear($key . '_end');
		static::clear($key . '_total');
		
	}
	
	/**
	 * Returns an array of 'name' => time pairs for all finished benchmarks.
	 * 
	 * @return array
	 * @since 1.0
	 */
	public static function summary() {
		
		$return = array();
		foreach(static::p()->register as $k => $v) {
			if(substr($k, -6) == '_total') $return[substr($k, 0, -6)] = static::read($k);
		}
		ksort($return);
		return $return;
		
	}
	
}

?>