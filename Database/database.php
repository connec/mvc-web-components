<?php
/**
 * Contains the Database class and related classes.
 *
 * @package database
 * @author Chris Connelly
 */
namespace MVCWebComponents\Database;
use \MVCWebComponents\Inflector;
use \MVCWebComponents\MVCException;
use \MVCWebComponents\BadArgumentException;

/**
 * Access point for database operations.
 *
 * Provides basic database abstraction functionality and additional useful features.
 *
 * @version 1.2
 */
Class Database {
	
	/**
	 * An instance of the database driver being used.
	 *
	 * @var object
	 * @since 1.0
	 */
	protected static $driver;
	
	/**
	 * An array of executed queries and details of the result.
	 *
	 * @var array
	 * @since 1.0
	 * @see query()
	 */
	protected static $queries = array();
	
	/**
	 * Debugging flag.
	 * 
	 * When set to true insert/update/delete queries are echo'd instead of executed.
	 * 
	 * @var bool
	 * @since 1.1
	 */
	public static $debugging = false;
	
	/**
	 * Instantiates and sets the driver.
	 *
	 * @param string $driverName The name of the driver to use.
	 * @return void
	 * @since 1.0
	 * @see $driver
	 * @throws InvalidDriverException Thrown when the given driver name is not a class or does not implement the correct interface.
	 */
	protected static function setDriver($driverName) {
		
		$driverName = '\MVCWebComponents\Database\\' . Inflector::camelize($driverName);
		if(!class_exists($driverName) or !in_array('MVCWebComponents\Database\DatabaseDriverInterface', class_implements($driverName))) throw new InvalidDriverException($driverName);
		self::$driver = new $driverName;
		
	}
	
	/**
	 * Connects to the database.
	 *
	 * Connect to a database using the defined driver and connection parameters, which are passed to the driver's own connect() function.
	 *
	 * @param string $driverName The name of the driver to use to connect.
	 * @param array $params An array of connection parameters to pass to the driver.
	 * @return bool True if a connection was successfully established, false otherwise.
	 * @since 1.0
	 * @throws DatabaseConnectionException Thrown when a connection cannot be established, as determined by the driver.
	 * @throws BadArgumentException Thrown when $driverName is not a string or $options is not an array.
	 */
	public static function connect($driverName, $options = array()) {
		
		if(!is_string($driverName)) throw new BadArgumentException("Database::connect() expects parameter 1 to be a string, '" . gettype($driverName) . "' given.");
		if(!is_array($options)) throw new BadArgumentException("Database::connect() expects parameter 2 to be an array, '" . gettype($options) . "' given.");
		
		self::setDriver($driverName);
		if(!self::$driver->connect($options)) throw new DatabaseConnectionException($driverName, self::getError());
		return true;
		
	}
	
	/**
	 * Performs a query on the database.
	 *
	 * Performs a query on the database through the driver, logs the query and it's result details.
	 *
	 * @param string $sql The SQL query.
	 * @return bool True on success, false on failure.
	 * @since 1.0
	 * @throws NoConnectionException Thrown when a connection has not been established.
	 */
	public static function query($sql) {
		
		if(!is_object(self::$driver)) throw new NoConnectionException('query');
		
		self::$queries[] = array();
		$query =& self::$queries[count(self::$queries) - 1];
		$query['sql'] = $sql;
		$query['insert_id'] = false;
		$query['num_result_rows'] = false;
		$query['num_affected_rows'] = false;
		$query['error'] = false;
		$query['time'] = 0;
		
		$start = microtime(true);
		
		if(self::$debugging and in_array(reset(explode(' ', $sql)), array('insert', 'update', 'delete'))) echo "$sql\n";
		elseif(!self::$driver->query($sql)) throw new BadQueryException($sql, self::getError());
		
		$query['insert_id'] = self::getInsertId();
		$query['num_result_rows'] = self::getNumResultRows();
		$query['num_affected_rows'] = self::getNumAffectedRows();
		$query['time'] = microtime(true) - $start;
		return true;
		
	}
	
	/**
	 * Returns a result row.
	 *
	 * Fetches a single row from the result, using the driver, and returns it as an array or object.
	 *
	 * @param string $type The type to return the row as, 'object', 'array' or a class name and optional constructor params.
	 * @return mixed The object/array result row.  False on failure.
	 * @since 1.0
	 * @throws NoConnectionException Thrown when a database connection has not been established.
	 * @throws BadArgumentException Thrown when the provided $type is not valid.
	 */
	public static function getRow($type = 'object') {
		
		if(!is_object(self::$driver)) throw new NoConnectionException('query');
		
		if($type == 'array') return self::$driver->getArray();
		elseif($type == 'object') return self::$driver->getObject();
		else {
			if(is_string($type) and class_exists($type)) return self::$driver->getObject($type);
			elseif(is_array($type) and isset($type[0]) and isset($type[1]) and is_string($type[0]) and is_array($type[1])) return self::$driver->getObject($type[0], $type[1]);
			else throw new BadArgumentException("Database::getRow() expects parameter 1 to be 'object', 'array', 'className' or array('className', array params), '" . print_r($type, true) . "' given.");
		}
		
		return false;
		
	}
	
	/**
	 * Returns all result rows as an array of rows.
	 *
	 * @param mixed $type The type of the row to return, 'object', 'array', 'className' or an array with the class name at the first index and an array of constructor arguments for the class in the second index.
	 * @return array The array of rows.
	 * @since 1.0
	 * @throws NoConnectionException Thrown when no database connection has been established.
	 */
	public static function getAll($type = 'object') {
		
		if(!is_object(self::$driver)) throw new NoConnectionException('query');
		
		$return = array();
		self::rewind();
		while($row = self::getRow($type)) $return[] = $row;
		return $return;
		
	}
	
	/**
	 * Resets the result pointer to the first row.
	 *
	 * @return void
	 * @since 1.0
	 * @throws NoConnectionException Thrown when no database connection has been established.
	 */
	public static function rewind() {
		
		if(!is_object(self::$driver)) throw new NoConnectionException('query');
		
		self::$driver->rewind();
		
	}
	
	/**
	 * Returns the error from the last query if one exists.
	 *
	 * @return mixed The error if one exists, false otherwise.
	 * @since 1.0
	 * @throws NoConnectionException Thrown when no database connection has been established.
	 */
	public static function getError() {
		
		if(!is_object(self::$driver)) throw new NoConnectionException('query');
		
		return self::$driver->getError();
		
	}
	
	/**
	 * Returns the insert id from the last query.
	 *
	 * @return mixed The insert id if one exists, false otherwise (i.e. last query was not INSERT).
	 * @since 1.0
	 * @throws NoConnectionException Thrown when no database connection has been established.
	 */
	public static function getInsertId() {
		
		if(!is_object(self::$driver)) throw new NoConnectionException('query');
		
		return self::$driver->getInsertId();
		
	}
	
	/**
	 * Returns the number of result rows.
	 *
	 * @return mixed The number of result rows if available, false otherwise.
	 * @since 1.0
	 * @throws NoConnectionException Thrown when no database connection has been established.
	 */
	public static function getNumResultRows() {
		
		if(!is_object(self::$driver)) throw new NoConnectionException('query');
		
		return self::$driver->getNumResultRows();
		
	}
	
	/**
	 * Gets the number of rows affected by the last query.
	 *
	 * @return mixed The number of rows affected by the last query, false if not available.
	 * @since 1.0
	 * @throws NoConnectionException Thrown when no database connection has been established.
	 */
	public static function getNumAffectedRows() {
		
		if(!is_object(self::$driver)) throw new NoConnectionException('query');
		
		return self::$driver->getNumAffectedRows();
		
	}
	
	/**
	 * Escapes a string using the driver's native escape function.
	 *
	 * @param string $string The string to escape.
	 * @return string The escaped string.
	 * @since 1.0
	 * @throws NoConnectionException Thrown when a database connection has not been established.
	 */
	public static function escape($string) {
		
		if(!is_object(self::$driver)) throw new NoConnectionException('query');
		
		return self::$driver->escape($string);
		
	}
	
	/**
	 * Returns the array of queries logged.
	 *
	 * @return array The array of queries.
	 * @since 1.0
	 * @see query()
	 */
	public static function getQueries() {
		
		return self::$queries;
		
	}
	
}

/**
 * Invalid driver exception.
 *
 * An exception thrown when {@link Database::setDriver()} encounters an invalid (e.g. undefined/doesn't implement DatabaseDriver interface) driver name.
 *
 * @subpackage exceptions
 * @version 1.0
 */
Class InvalidDriverException extends MVCException {
	
	/**
	 * Assigns the message.
	 * 
	 * @param string $driverName The invalid driver name.
	 * @return void
	 * @since 1.0
	 */
	public function __construct($driverName) {
		
		$this->message = "Invalid driver name given to Database::setDriver(), ensure the class '$driverName' exists and implements the DatabaseDriver interface.";
		
	}
	
}

/**
 * Database connection exception.
 *
 * An exception thrown when {@link Database::connect()} encounters an error.
 *
 * @subpackage exceptions
 * @version 1.0
 */
Class DatabaseConnectionExcepion extends MVCException {
	
	/**
	 * Assigns the message.
	 * 
	 * @param string $driverName The name of the driver used in the connection attempt.
	 * @param string $driverError The error given by the driver.
	 * @return void
	 * @since 1.0
	 */
	public function __construct($driverName, $driverError) {
		
		$this->message = "Failed to connect to database, $driverName said: $driverError";
		
	}
	
}

/**
 * Bad query exception.
 * 
 * An exception thrown when {@link Database::query()} encounters an error.
 * 
 * @subpackage exceptions
 * @version 1.0
 */
class BadQueryException extends MVCException {
	
	/**
	 * Assigns the message.
	 * 
	 * @param string $sql The sql that failed.
	 * @param string $error The error as given by the driver.
	 * @return void
	 * @since 1.0
	 */
	public function __construct($sql, $error) {
		
		$this->message = "Database query '$sql' failed.  Error: '$error'.";
		
	}
	
}

/**
 * No connection exception.
 *
 * An exception thrown when a function requiring an active connection is called and a connection has not been established.
 *
 * @subpackage exceptions
 * @version 1.0
 */
Class NoConnectionException extends MVCException {
	
	/**
	 * Assigns the message.
	 * 
	 * @param string $function The name of the function that triggered this exception.
	 * @return void
	 * @since 1.0
	 */
	public function __construct($function) {
		
		$this->message = "The function '$function' requires an active database connection, establish one first with Database::connect().";
		
	}
	
}

/**
 * Interface that database drivers must implement.
 * 
 * Provides abstract methods for all the methods used by Database.
 *
 * @version 1.1
 */

Interface DatabaseDriverInterface {
	
	/**
	 * Connects to the database and selects a database.
	 *
	 * @param array $options An array of connection options.
	 * @return bool True if connection succeeded, false otherwise.  A return value of false will throw a DatabaseConnectionException exception.
	 * @since 1.0
	 */
	public function connect($options);
	
	/**
	 * Executes a query against the data source.
	 *
	 * @param string $sql The SQL query.
	 * @return bool True if query was successful, false otherwise.
	 * @since 1.0
	 */
	public function query($sql);
	
	/**
	 * Gets a single row from a query result as an array.
	 * 
	 * @return mixed An array result on success, false on failure.
	 * @since 1.1
	 */
	public function getArray();
	
	/**
	 * Gets a single row from a query result as an object.
	 * 
	 * @param string $className The name of the class to instantiate, defaults to StdClass.
	 * @param array $params An array of parameters for the $className class constructor.
	 * @return mixed An object result on success, false on failure.
	 * @since 1.1
	 */
	public function getObject($className = 'StdClass', $params = array());
	
	/**
	 * Returns the pointer of the result to the first (0) row.
	 *
	 * @return void
	 * @since 1.0
	 */
	public function rewind();
	
	/**
	 * Return a string error from the last query.
	 *
	 * @return mixed String error message or false if last query was successful.
	 * @since 1.0
	 */
	public function getError();
	
	/**
	 * Returns the insert id of the last query.
	 *
	 * @return mixed Integer insert ID if available, false otherwise.
	 * @since 1.0
	 */
	public function getInsertId();
	
	/**
	 * Returns the number of rows in a result.
	 *
	 * @return mixed Integer number of rows if available, false otherwise.
	 * @since 1.0
	 */
	public function getNumResultRows();
	
	/**
	 * Returns the number of affected rows from the last query.
	 *
	 * @return mixed Integer number of affected rows if available, false otherwise.
	 * @since 1.0
	 */
	public function getNumAffectedRows();
	
	/**
	 * Returns a string escaped using the vendor specific escape function.
	 *
	 * @param string $string The string to escape.
	 * @return string The escaped string.
	 * @since 1.0
	 */
	public function escape($string);
	
}

?>