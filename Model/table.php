<?php

/**
 * Contains the Table class, used to cache table data for a Model.
 * 
 * @package MVCWebComponents.Model
 * @author Chris Connelly
 */
namespace MVCWebComponents\Model;
use MVCWebComponents\Database\Database,
	MVCWebComponents\BadArgumentException;

/**
 * A simple class that fetches and represents the structure of a database table.
 * 
 * @version 0.5.1
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
	 * The name of the Model this table represents.
	 * 
	 * @var string
	 * @since 0.5
	 */
	protected $model;
	
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
	public static function &instance($tableName, $model) {
		
		if(!isset(self::$tables[$tableName][$model])) self::$tables[$tableName][$model] = new Table($tableName, $model);
		return self::$tables[$tableName][$model];
		
	}
	
	/**
	 * Fetches and stores the table's schema from the provided table name.
	 *
	 * @param string $tableName The name of the table to represent.
	 * @return void
	 * @since 0.1
	 */
	protected function __construct($tableName, $model) {
		
		$this->name = $tableName;
		$this->model = $model;
		
		Database::query("describe `$tableName`");
		$this->schema = Database::getAll('array');
		
		foreach($this->schema as &$field) {
			$this->fields[] = $field['Field'];
			if($field['Key'] == 'PRI') $this->primaryKey = $field['Field'];
		}
		
		$this->updateRowCount();
		
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
	
	/**
	 * Finds table entries matching given options.
	 * 
	 * @param options
	 * @return Model|Model[] The result of the find query.
	 * @since 0.5
	 */
	public function find($options = array()) {
		
		// Fill any unset options with defaults.
		$defaults = array(
			'conditions' => array(),
			'type' => 'all',
			'orderBy' => '',
			'limit' => 0,
			'operator' => 'and'
		);
		$options = array_merge($defaults, $options);
		
		// Parse the conditions into pure SQL.
		foreach($options['conditions'] as $key => $value) {
			if(is_string($key)) {
				// Parse the value for operators.
				if(!is_string($value)) $value = strval($value);
				if(in_array($operator = substr($value, 0, 3), array('<> ', 'in ', '!= ', '<= ', '>= '))) {
					$operator = trim($operator);
					if($operator == '!=') $operator = '<>';
					if($operator == 'in') $value = "('" . implode("', '", explode(',', Database::escape(substr($value, 3)))) . "')";
					else $value = "'" . Database::escape(substr($value, 3)) . "'";
				}elseif(in_array($operator = substr($value, 0, 2), array('< ', '> ', '~ ', '= '))) {
					$operator = trim($operator);
					if($operator == '~') $operator = 'like';
					$value = "'" . Database::escape(substr($value, 2)) . "'";
				}else {
					$operator = '=';
					$value = "'" . Database::escape($value) . "'";
				}
				$options['conditions'][] = "`" . $this->name . "`.`$key` $operator $value";
				unset($options['conditions'][$key]);
			}
		}
		
		// Start building the query.
		$query = 'select * from `' . $this->name . "` where " . (implode(' ' . $options['operator'] . ' ', $options['conditions']) ?: '1');
		
		// Append the other options.
		if($options['orderBy']) {
			@list($field, $dir) = explode(' ', $options['orderBy']);
			if($dir != 'asc' and $dir != 'desc') $dir = 'asc';
			$query .= " order by `" . $this->name . "`.`$field` $dir";
		}
		if($options['limit']) $query .= ' limit ' . $options['limit'];
		
		// Perform the query.
		Database::query($query);
		$class = $this->model;
		if($options['type'] == 'first') {
			$return = Database::getRow('array');
			if($return) $return = new $class($return);
			else $return = null;
		}else {
			$return = Database::getAll('array');
			if(!empty($return)) $return = array_map(function($row) use($class) {return new $class($row);}, $return);
		}
		
		return $return;
		
	}
	
	/**
	 * Inserts a record into the database.
	 * 
	 * @param  Model A model instance to insert.
	 * @return mixed The insert id on success, true if table doesn't use AI, false if insert failed.
	 * @since 0.5.1
	 * @throws BadArgumentException Thrown when $record is not a Model.
	 */
	public function insert($record) {
		
		if(!($record instanceof Model)) throw new BadArgumentException('Table::insert() can only insert Model instances.');
		
		$fields = array();
		$values = array();
		foreach($this->fields as $field) {
			if(!isset($record->$field) or $record->$field === null) continue;
			$fields[] = "`$field`";
			$values[] = "'" . Database::escape($record->$field) . "'";
		}
		
		$query = 'insert into `' . $this->name . "` (" . implode(', ', $fields) . ') values (' . implode(', ', $values) . ')';
		if(Database::query($query)) {
			$this->rowCount += 1;
			if(Database::getInsertId()) return Database::getInsertId();
			return true;
		}else return false;
		
	}
	
	/**
	 * Updates a record in the database.
	 * 
	 * Updates the record in database, identified by the primary key, with the other values in the record.
	 * 
	 * @param  Model The record to update.
	 * @return bool  True on success, false on failure.
	 * @since  0.5.1
	 * @throws BadArgumentException Thrown when $record is not a model or does not contain a value for the primary key.
	 */
	public function update($record) {
		
		if(!($record instanceof Model)) throw new BadArgumentException('Table::update() can only update Model instances.');
		if(!isset($record->primary_key))  throw new BadArgumentException('Table::update() requires the given record to include the primary key of the item to update.');
		
		$updates = array();
		foreach($this->fields as $field) {
			if($field == $this->primaryKey) continue;
			$updates[] = "`$field` = '" . Database::escape($record->$field) . "'";
		}
		
		$query = 'update `' . $this->name . '` set ' . implode(', ', $updates) . ' where `' . $this->primaryKey . "` = '$record->primary_key' limit 1";
		if(Database::query($query)) return true;
		else return false;
		
	}
	
	/**
	 * Deletes a record from the database.
	 * 
	 * @return bool True on success, false on failure.
	 * @since 0.5.1
	 * @throws BadArgumentException Thrown when $data is not an object or it does not contain a value for the primary key.
	 */
	public function delete($record) {
		
		if(!isset($record->primary_key)) throw new BadArgumentException('Table::delete() requires the supplied data to include the primary key of the item to update.');
		$query = 'delete from `' . $this->name .'` where `' . $this->primaryKey . "` = '$record->primary_key' limit 1";
		if(Database::query($query)) {
			$this->rowCount -= 1;
			return true;
		}else return false;
		
	}
	
}

?>