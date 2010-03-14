<?php

/**
 * Contains the Table class, used to cache table data for a Model.
 * 
 * @package MVCWebComponents.Model
 * @author Chris Connelly
 */
namespace MVCWebComponents\Model;
use MVCWebComponents\Database\Database;

/**
 * A simple class that fetches and represents the structure of a database table.
 * 
 * @version 0.4
 */
class Table {
	
	/**
	 * An array of tables indexed by table name to allow singleton functionality.
	 * 
	 * @var array
	 * @since 0.1
	 */
	protected static $tables;
	
	/**
	 * The name of the table in the database.
	 * 
	 * @var string
	 * @since 0.1
	 */
	protected $name;
	
	/**
	 * An array of the fields in the table.
	 * 
	 * @var array
	 * @since 0.1
	 */
	protected $fields = array();
	
	/**
	 * The name of the primary key field.
	 * 
	 * @var string
	 * @since 0.1
	 */
	protected $primaryKey;
	
	/**
	 * An array representing the database schema as described by the database.
	 * 
	 * @var array
	 * @since 0.1
	 */
	protected $schema = array();
	
	/**
	 * The total number of rows in the table.
	 * 
	 * @var int
	 * @since 0.4
	 */
	protected $rowCount;
	
	/**
	 * Getter for {@link $name}.
	 * 
	 * @return string The value of {@link $name}.
	 * @since 0.2
	 */
	public function getName() {
		
		return $this->name;
		
	}
	
	/**
	 * Getter for {@link $fields}.
	 * 
	 * @return array The value of {@link $fields}.
	 * @since 0.2
	 */
	public function getFields() {
		
		return $this->fields;
		
	}
	
	/**
	 * Getter for {@link $primaryKey}.
	 * 
	 * @return string The value of {@link $primaryKey}.
	 * @since 0.2
	 */
	public function getPrimaryKey() {
		
		return $this->primaryKey;
		
	}
	
	/**
	 * Getter for {@link $schema}.
	 * 
	 * @return array The value of {@link $schema}.
	 * @since 0.2
	 */
	public function getSchema() {
		
		return $this->schema;
		
	}
	
	/**
	 * Getter for {@link $rowCount}.
	 * 
	 * @return int
	 * @since 0.4
	 */
	public function getRowCount() {
		
		return $this->rowCount;
		
	}
	
	/**
	 * Get a hash of the default fields.
	 * 
	 * @return array
	 * @since 0.3
	 */
	public function getDefaultRecord() {
		
		$return = array();
		foreach($this->schema as $field) {
			if($field['Field'] == $this->getPrimaryKey()) continue;
			$return[$field['Field']] = $field['Default'];
		}
		return $return;
		
	}
	
	/**
	 * Returns an instance of Table identified by $tableName.
	 * 
	 * @param string $tableName The name of the table.
	 * @return &object The table instance, by reference.
	 * @since 0.1
	 */
	public static function &instance($tableName) {
		
		if(!isset(self::$tables[$tableName])) self::$tables[$tableName] = new Table($tableName);
		return self::$tables[$tableName];
		
	}
	
	/**
	 * Fetches and stores the table's schema from the provided table name.
	 *
	 * @param string $tableName The name of the table to represent.
	 * @return void
	 * @since 0.1
	 */
	protected function __construct($tableName) {
		
		$this->name = $tableName;
		
		Database::query("describe `$tableName`");
		$this->schema = Database::getAll('array');
		
		foreach($this->schema as &$field) {
			$this->fields[] = $field['Field'];
			if($field['Key'] == 'PRI') $this->primaryKey = $field['Field'];
		}
		
		Database::query("select count(`$this->primaryKey`) from `$this->name`");
		$result = Database::getRow('array');
		$this->rowCount = $result['count(`id`)'];
		
	}
	
	/**
	 * Updates the row count.
	 * 
	 * @return int The updated row count.
	 * @since 0.4
	 */
	public function updateRowCount() {
		
		Database::query("select count(`$this->primaryKey`) from `$this->name`");
		$result = Database::getRow('array');
		return $this->rowCount = $result['count(`id`)'];
		
	}
	
}

?>