<?php

/**
 * Contains the session class.
 * 
 * @package utilities
 * @author Chris Connelly
 */
namespace MVCWebComponents;

/**
 * Manages reading from/writing to $_SESSION.
 * 
 * @version 1.2
 */
class Session extends Set {
	
	/**
	 * Overide Set's default behaviour to use $_SESSION instead of an empty array.
	 * 
	 * @return void
	 * @since 1.1
	 */
	protected static function setInit() {
		
		parent::__init();
		if(!isset(static::p()->register)) static::p()->register =& $_SESSION;
		
	}
	
	/**
	 * Start session PHP's session handling.
	 * 
	 * @return void
	 * @since 1.2
	 */
	public static function start() {
		
		session_start();
		static::setInit();
		
	}
	
	/**
	 * Get the session id.
	 * 
	 * @return string The session id.
	 * @since 1.2
	 */
	public static function getId() {
		
		return session_id();
		
	}
	
	/**
	 * Set the session id.
	 * 
	 * @return void
	 * @since 1.2
	 */
	public static function setId($id) {
		
		session_id($id);
		
	}
	
	/**
	 * Regenerate the session ID.
	 * 
	 * @param bool $deleteOldSession
	 * @return bool True on success, false on failure.
	 * @since 1.2
	 */
	public static function regenerateId($deleteOldSession = false) {
		
		session_regenerate_id($deleteOldSession);
		
	}
	
	/**
	 * End session handling and destroy the session.
	 * 
	 * @return bool True on success, false on failure.
	 * @since 1.2
	 */
	public static function destroy() {
		
		$_SESSION = array();
		if(ini_get('session.use_cookies')) {
			$params = session_get_cookie_params();
			setcookie(session_name(), '', time() - 42000,
				$params['path'], $params['domain'],
				$params['secure'], $params['httponly']);
		}
		return session_destroy();
		
	}
	
}

?>