<?php

/**
 * Contains the MysqliDriver class and related exceptions.
 *
 * @package database
 * @subpackage drivers
 * @author Chris Connelly
 */
namespace MVCWebComponents\Database;
use MVCWebComponents\MVCException, MVCWebComponents\BadArgumentException, MySQLi, MySQLi_Result;

/**
 * Mysqli database driver.
 *
 * MysqliDriver class to allow the Database class to interface with a MySQL database using MySQLi functions.
 * 
 * @version 1.2
 */

Class MysqliDriver implements DatabaseDriverInterface {
	
	/**
	 * Instance of the MySQLi class representing the database connection.
	 *
	 * @var MySQLi
	 * @since 1.0
	 */
	private $mysqli;
	
	/**
	 * Instance of the MySQLi_Result class representing the result of a query.
	 *
	 * @var MySQLi_Result
	 * @since 1.0
	 */
	private $result;
	
	/**
	 * Connect to a MySQL database.
	 *
	 * Creates a connection to the MySQL database, instantiates {@link $mysqli}.
	 * 
	 * Possible keys for $params are:
	 * - server: The address of the server to connect to.
	 * - user: The username to connect with.
	 * - password: The password to connect with.
	 * - database: Required.  The database to select.
	 *
	 * @param mixed $params An array of parameters to connect with or a database name to select (connects with default options).
	 * @return bool True on connection success, false on failure.
	 * @since 1.0
	 */
	public function connect($params) {
		
		$defaults = array(
			'server' => null,
			'user' => null,
			'password' => null,
		);
		if(is_string($params)) $params = array('database' => $params);
		$params = array_merge($defaults, $params);
		if(!isset($params['database'])) throw new BadArgumentException("MysqliDriver::connect() requires 'database' key to be present in parameter 1.");
		
		$this->mysqli = new MySQLi($params['server'], $params['user'], $params['password'], $params['database']);
		
		if(!$this->mysqli or $this->mysqli->connect_error) throw new DatabaseConnectionException('MysqliDriver', $this->mysqli->connect_error);
		return true;
	}
	
	/**
	 * Execute a query against the database and store the result.
	 *
	 * @param string $sql The sql query to execute.
	 * @return bool True on query success, false on failure.
	 * @since 1.0
	 */
	public function query($sql) {
		
		if($this->result instanceof MySQLi_Result) $this->result->free();
		return (bool) ($this->result = $this->mysqli->query($sql));
		
	}
	
	/**
	 * Returns a row from the {@link $result} as an array.
	 * 
	 * @return mixed An array result on success, false on failure.
	 * @since 1.1
	 */
	public function getArray() {
		
		if(!($this->result instanceof MySQLi_Result)) {
			throw new MySQLiBadResultException();
			return false;
		}
		
		$return = $this->result->fetch_assoc();
		if(!empty($return)) foreach($return as &$value) if(is_numeric($value)) $value = (int)$value;
		return $return;
		
	}
	
	/**
	 * Returns a row from the {@link $result} as an object.
	 * 
	 * @param string $className The name of the class to return.
	 * @param array $params Parameters to pass to the constructor of $className.
	 * @return mixed An object result on success, false on failure.
	 * @since 1.1
	 */
	public function getObject($className = 'StdClass', $params = null) {
		
		if(!($this->result instanceof MySQLi_Result)) {
			throw new MySQLiBadResultException();
			return false;
		}
		
		$return = (empty($params) ? $this->result->fetch_object($className) : $this->result->fetch_object($className, $params));
		if(!empty($return)) foreach($return as &$value) if(is_numeric($value)) $value = (int)$value;
		return $return;
		
	}
	
	/**
	 * Rewinds the result pointer to the beginning.
	 *
	 * @return void
	 * @since 1.0
	 */
	public function rewind() {
		
		if($this->result instanceof MySQLi_Result) $this->result->data_seek(0);
		else throw new MySQLiBadResultException();
		
	}
	
	/**
	 * Returns the current MySQLi error if set.
	 *
	 * @return mixed The error, if one occured, false otherwise.
	 * @since 1.0
	 */
	public function getError() {
		
		return $this->mysqli->error ?: false;
		
	}
	
	/**
	 * Returns the insert id if available.
	 *
	 * @return mixed Integer insert id or false if not available.
	 * @since 1.0
	 */
	public function getInsertId() {
		
		return $this->mysqli->insert_id ?: false;
		
	}
	
	/**
	 * Returns the number of rows in {@link $result}.
	 *
	 * @return mixed Integer num result rows if available, false otherwise.
	 * @since 1.0
	 */
	public function getNumResultRows() {
		
		return $this->result instanceof MySQLi_Result ? $this->result->num_rows : false;
		
	}
	
	/**
	 * Returns the number of rows in the database affected by the previous query.
	 *
	 * @return mixed Integer num affected rows if available, false otherwise.
	 * @since 1.0
	 */
	public function getNumAffectedRows() {
		
		return $this->mysqli->affected_rows > -1 ? $this->mysqli->affected_rows : false;
		
	}
	
	/**
	 * Escapes a string using MySQLi->escape_string().
	 *
	 * @param string $string The string to escape.
	 * @return string The escaped string.
	 * @since 1.0
	 */
	public function escape($string) {
		
		return $this->mysqli->escape_string($string);
		
	}
	
}

/**
 * MySQLi bad result exception.
 *
 * An exception thrown when MysqliDriver tries to use a none MySQLi_Result {@link $result} where one is required.
 *
 * @subpackage exceptions
 * @version 1.0
 */
Class MySQLiBadResultException extends MVCException {
	
	/**
	 * Assigns the message.
	 * 
	 * @return void
	 * @since 1.0
	 */
	public function __construct() {
		
		$this->message = 'Invalid value for $result encountered.';
		
	}
	
}

?>