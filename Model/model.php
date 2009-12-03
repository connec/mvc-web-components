<?php

/**
 * Contains the Model class and related classes.
 *
 * @package mvc-web-components.components
 * @author Chris Connelly
 */
namespace MVCWebComponents\Model;
use MVCWebComponents\MVCException, MVCWebComponents\BadArgumentException, MVCWebComponents\Inflector, MVCWebComponents\Database\Database;

/**
 * The Model class is an extensible class to allow 'zero configuration' CRUD + extras database interaction.
 * 
 * Once a table has been created in the database all that is needed to allow CRUD functionality is:
 * <code>
 * class User {}
 * </code>
 * 
 * Conventions assumed are:
 * - The class (and thus, model) name is the camel-cased singular of the table name.  Thus a table called 'users' should have a model called 'User'.
 * - There is a primary key.  The field is gotten through the table's schema.  Some functionality will work without a primary key but this is not explicitly tested.
 * - Single field primary keys.  No support for multi-fielded primary keys.
 * 
 * The table can be overidden by adding some configuration:
 * <code>
 * class User {
 *    protected $tableName = 'ApplicationUser'; // Or whatever...
 * }
 * </code>
 * 
 * For specific operations see the method documentations.
 * 
 * @version 0.8
 */
abstract class Model {
	
	/**
	 * An array of Model data, due to PHP's inability to dynamically create static properties.
	 * 
	 * @var array
	 * @since 0.8
	 */
	protected static $models = array();
	
	/**
	 * Table name to use.
	 * 
	 * If this is set it is used as the table name instead of inferring it from the model name.
	 * 
	 * @var string
	 * @since 0.3
	 */
	protected static $tableName;
	
	/**
	 * An array of validation rules to apply.
	 * 
	 * Format:
	 * <code>
	 * $validate = array(
	 *    'FieldName' => array('rule1', 'rule2' => 'arg')
	 * );
	 * </code>
	 * 
	 * Available validation rules include:
	 * - minlength: Defines a minimum length the field must meet. <code>'minlength' => int</code>.
	 * - maxlength: Defines a maximum length the field can be.  <code>'maxlength' => int</code>.
	 * - length: Specifies an exact length a field must meet.  <code>'length' => int</code>.
	 * - unique: Checks that no record exists in the table with the same field value. <code>'unique'</code>.
	 * - date: Checks that the value is a string date parseable by strtotime(). <code>'date'</code>.
	 * - dateformat: Checks that the value is a string date in the format specified.  Format specified same as PHP's date() function.  Only formats compatable with strtotime will work correctly.  <code>'dateformat' => 'Y-m-d H:i:s'</code>.
	 * - regex: Checks the the value matches the given regex. <code>'regex' => '.*'</code>.
	 * - callback: Passes the value of the field and any additional arguments to the specified callback.  <code>'callback' => array('Class', 'function')</code> OR <code>'callback' => array(array('Class', 'function'), 'arg1', 'arg2')</code>.  Callback should return boolean true on pass, false on fail.
	 * - required: Checks that the value is not empty.
	 * 
	 * @var array
	 * @since 0.2
	 * @see validate()
	 */
	protected static $validate = array();
	
	/**
	 * Describes 'has one' relationships.
	 * 
	 * Describes relationships with other models where the foreign key in the other model equals the primary key of this model.  Limited to one match.
	 * 
	 * Format:
	 * <code>
	 * $hasOne = array(
	 *    'Alias' => array( // The alias will be given to the model used for the relationship and will be the field name in the result (e.g. $user->Alias->dataz).
	 *       'model' => 'ModelName',
	 *       'foreignKey' => 'alias_id', // The 'foreign key': for has relationships this is in the other model, for belongs relationships it's in this model (bit of a misnomer).
	 *       'options' => array() // Additional options for the find, same format as $options for Model::find().  Some values are overriden such as 'limit' = 1, 'type' = 'first' etc.
	 *   ),
	 *   'OtherModelName', // Alternatively the alias and foreign key can be inferred from the model name.
	 *   'AnotherModelName'
	 * );
	 * </code>
	 * 
	 * @var array
	 * @since 0.3
	 * @see $hasMany, $belongsTo, find()
	 */
	protected static $hasOne = array();
	
	/**
	 * Describes 'has many' relationships.
	 * 
	 * Describes relationships with other models where the foreign key in the other model equals the primary key of this model.  Results in an array of matches.
	 * 
	 * Format: see {@link $hasOne}.
	 * 
	 * @var array
	 * @since 0.3
	 * @see $hasOne, $belongsTo, find()
	 */
	protected static $hasMany = array();
	
	/**
	 * Described 'belongs to' relationships.
	 * 
	 * Describes relationships with other models where the 'foreign key' in this model equals the primary key of the other model.  Limited to one match.
	 * 
	 * Format: see {@link $hasOne}.
	 * 
	 * @var array
	 * @since 0.3
	 * @see $hasOne, $hasMany, find()
	 */
	protected static $belongsTo = array();
	
	/**
	 * Returns the StdClass metadata for this model.
	 * 
	 * @return StdClass
	 * @since 0.8
	 */
	public static function &properties() {
		
		return self::$models[get_called_class()];
		
	}
	
	/**
	 * Shorthand alias for {@link properties()}.
	 * 
	 * @return StdClass
	 * @since 0.8
	 */
	public static function &p() {
		
		return static::properties();
		
	}
	
	/**
	 * Returns the (fully qualified) name of the model (class).
	 * 
	 * @return string The name of the model.
	 * @since 0.1
	 */
	public static function getName() {
		
		static::__init();
		return get_called_class();
		
	}
	
	/**
	 * Returns the name of the table the model is using.
	 * 
	 * @return string The table name.
	 * @since 0.1
	 * @see Table
	 */
	public static function getTableName() {
		
		static::__init();
		return static::properties()->table->getName();
		
	}
	
	/**
	 * Returns the primary key of the table.
	 * 
	 * @return string The primary key.
	 * @since 0.1
	 * @see Table
	 */
	public static function getPrimaryKey() {
		
		static::__init();
		return static::properties()->table->getPrimaryKey();
		
	}
	
	/**
	 * Returns an array of fields in this model's table.
	 * 
	 * @return array An array of fields in the table.
	 * @since 0.4
	 * @see Table
	 */
	public static function getFields() {
		
		static::__init();
		return static::properties()->table->getFields();
		
	}
	
	/**
	 * Returns an StdClass with all the current static values (very useful for debugging)
	 * 
	 * @param bool $return When true, returns the object instead of dumping it.
	 * @return StdClass An object containing the current values of useful static properties.
	 * @since 0.8
	 */
	public static function dump($dump = true) {
		
		static::__init();
		
		$return = static::properties();
		
		if(!$dump) return $return;
		var_dump($return);
		
	}
	
	/**
	 * Sets up the model for use.
	 * 
	 * @return void
	 * @since 0.8
	 * @throws BadConfigurationException Thrown when an invalid relationship definition is encountered.
	 */
	public static function __init() {
		
		if(!isset(self::$models[get_called_class()])) self::$models[get_called_class()] = new \StdClass;
		else return;
		
		// Store the model name (sans namespace) in the metadata.
		static::properties()->name = @end(explode('\\', static::getName()));
		
		if(!static::$tableName) static::properties()->tableName = Inflector::tableize(static::properties()->name);
		else static::properties()->tableName = static::$tableName;
		
		static::properties()->table = Table::getInstance(static::properties()->tableName);
		
		static::normalizeRelations();
		
	}
	
	/**
	 * Normalizes the relation arrays to the standard form:
	 * <code>
	 * array(
	 *   'Alias' => array(
	 *      'model' => 'ModelName',
	 *      'foreignKey' => 'alias_id',
	 *      'options' => array() // Options to pass to the find() method.
	 *   )
	 * );
	 * 
	 * @return void
	 * @since 0.8
	 */
	protected static function normalizeRelations() {
		
		static::properties()->hasOne = static::$hasOne;
		static::properties()->hasMany = static::$hasMany;
		static::properties()->belongsTo = static::$belongsTo;
		
		$relationConfig = array('hasOne' => &static::properties()->hasOne, 'hasMany' => &static::properties()->hasMany, 'belongsTo' => &static::properties()->belongsTo);
		foreach($relationConfig as $relationType => &$relations) {
			foreach($relations as $index => &$relation) {
				if(is_int($index) and is_string($relation)) {
					$alias = $model = $relation;
					$relations[$alias] = array();
					$relation =& $relations[$alias];
					unset($relations[$index]);
				}elseif(is_string($index) and is_array($relation)) $alias = $model = $index;
				else throw new BadConfigurationException("Invalid $relationType configuration in model " . static::getName() . ', see documentation for correct format.');
				
				if(isset($relation['model'])) $model = $relation['model'];
				else $relation['model'] = $model;
				
				// Deal with namespacing
				if(!class_exists($relation['model'])) {
					// Try prefixing the namespace of the current model.
					$namespace = str_replace(static::p()->name, '', get_called_class());
					$relation['model'] = "$namespace$model";
					
					// Run a method with that class, if it doesn't exist the user will have to sort it out.
					$relation['model']::getName();
				}
				
				$key = Inflector::underscore(static::properties()->name) . '_' . static::getPrimaryKey();
				if($relationType == 'belongsTo') $key = Inflector::underscore($alias) . '_' . $relation['model']::getPrimaryKey();
				if(isset($relation['foreignKey'])) $key = $relation['foreignKey'];
				else $relation['foreignKey'] = $key;
				
				if(!isset($relation['options'])) $relation['options'] = array();
				if($relationType != 'hasMany') {
					$relation['options']['type'] = 'first';
					$relation['options']['limit'] = 1;
				}
			}
		}
		
	}
	
	/**
	 * Overloads method calling to allow pretty find functions.
	 * 
	 * Find functions format:
	 * - findAll($options): Returns an array of results matching $options.  Same as default setting of {@link find()}.
	 * - findFirst($options): Returns a single result matching $options.
	 * - findAllByFieldName($fieldValue, $options): Returns an array of results with `field_name` = $fieldValue and matching $options.
	 * - findFirstByFieldName($fieldValue, $options): Returns a single object result with `field_name` = $fieldValue and matching $options.
	 * 
	 * @param string $name The name of the method being called.
	 * @param array $args An array of the arguments passed to the method.
	 * @return mixed The result of the final called function.
	 * @since 0.2
	 * @see find()
	 */
	public static function __callStatic($name, $args) {
		
		$methodParts = explode('_', Inflector::underscore($name));
		
		$model = static::getName();
		$error = function() use ($name, $model) {trigger_error("Call to undefined method " . $model . "::" . $name . "()", E_USER_ERROR);};
		if(array_shift($methodParts) != 'find') $error();
				
		$options = array();
		switch(array_shift($methodParts)) {
			case 'first':
				$options['type'] = 'first';
				break;
			case 'all':
				$options['type'] = 'all';
				break;
			default:
				$error();
				break;
		}
		if(empty($methodParts)) {
			if(!isset($args[0]) or !is_array($args[0])) $args[0] = array();
			return call_user_func(array(static::getName(), 'find'), array_merge($args[0], $options));
		}
		
		if(array_shift($methodParts) != 'by') $error();
		if(empty($methodParts)) $error();
		
		$field = implode('_', $methodParts);
		if(!in_array($field, static::getFields())) $error();
		$options['conditions'] = array($field => $args[0]);
		if(!isset($args[1]) or !is_array($args[1])) $args[1] = array();
		if(isset($args[1]['conditions'])) $options['conditions'] = array_merge($args[1]['conditions'], $options['conditions']);
		return static::find(array_merge($args[1], $options));
		
	}
	
	/**
	 * Returns a result from the database, selected and formatted according to $options.
	 * 
	 * $options is an array of the following format:
	 * <code>
	 * $options = array(
	 *    'option' => 'value'
	 * );
	 * </code>
	 * 'option' keys are:
	 * - conditions: An array of sql conditions or 'field' => 'Value' pairings to use in the where clause.
	 * - fields: An array of fields to include in the result.  By default, all the tables fields are used.
	 * - type: Either 'first' or 'all'.  First assumes a limit of 1 and returns that result, all returns an array of results found.
	 * - return: The class of object to return, should be a string class name or an array of form (string className, array constructor_params).  Defaults to StdClass.
	 * - orderBy: A field name the result should be sorted by.
	 * - limit: The number of results the query should return.  A limit of 0 means no limit.  A limit can also be a string 'start,end' to specify the selection of results to return.
	 * - operator: The operator ('and' or 'or') to use when combining 'field' => 'value' pairs.
	 * - cascade: Whether or not the query should relate the result using hasMany, hasOne and belongsTo configurations.
	 * - processed: Used in cascading, an array of aliases already processed.
	 * For 'field' => 'Value' pairings, the value string can contain an operator (<, >, <>, !=, in, ~) as the first character(s) e.g. '< 1', '~ HeLlO', 'in Value1,Value2,Value3'.
	 * 
	 * @param array $options An array of options for the find.
	 * @return mixed Array or object of results.
	 * @since 0.1
	 */
	public static function find($options = array()) {
		
		// Fill any unset options with defaults.
		$defaults = array(
			'conditions' => array(),
			'fields' => '*',
			'type' => 'all',
			'return' => 'StdClass',
			'orderBy' => '',
			'limit' => 0,
			'operator' => 'and',
			'cascade' => true,
			'processed' => array(static::getName())
		);
		$options = array_merge($defaults, $options);
		
		// Get the SQL query.
		$query = static::buildSQL($options);
		
		// Execute query and store result.
		Database::query($query);
		if($options['type'] == 'first') $return = Database::getRow($options['return']);
		else $return = Database::getAll($options['return']);
		
		// Relate the result if in the options.
		if($options['cascade']) static::findRelated($return, $options['processed']);
		
		// Return it.
		return $return;
		
	}
	
	/**
	 * Constructs an SQL query from given options.
	 * 
	 * @param array $options The options as passed to find.
	 * @return string An SQL query string.
	 * @since 0.8
	 */
	protected static function buildSQL($options) {
		
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
				$options['conditions'][] = "`" . static::getName() . "`.`$key` $operator $value";
				unset($options['conditions'][$key]);
			}
		}
		
		// Sort out the 'fields' options.
		if(is_array($options['fields'])) $options['fields'] = "`" . static::getName() . "`.`" . implode("`,`" . static::getName() . "`.`", $options['fields']) . '`';
		
		// Start building the query.
		$query = 'select ' . $options['fields'] . ' from `' . static::getTableName() . "` as `" . static::getName() . "` where " . (implode(' ' . $options['operator'] . ' ', $options['conditions']) ?: '1');
		
		// Append the other options.
		if($options['orderBy']) {
			@list($field, $dir) = explode(' ', $options['orderBy']);
			if($dir != 'asc' and $dir != 'desc') $dir = 'asc';
			$query .= " order by `" . static::getName() . "`.`$field` $dir";
		}
		if($options['limit']) $query .= ' limit ' . $options['limit'];
		
		return $query;
		
	}
	
	/**
	 * Adds related data to a given result.
	 * 
	 * Adds related data to a given result as defined in {@link $hasOne}, {@link $hasMany} and {@link $belongsTo} configurations.
	 * 
	 * @param mixed $result The result to relate, should be an object or array.
	 * @param array $processed An array of model names already processed.  Prevents infinite looping.
	 * @return void
	 * @since 0.3
	 * @throws BadArgumentException Thrown when $result is not an object or array.
	 */
	protected static function findRelated(&$result, $processed) {
		
		if(empty($result) or (empty(static::p()->hasOne) and empty(static::p()->hasMany) and empty(static::p()->belongsTo))) return;
		if(is_array($result) and isset($result[0])) foreach($result as &$_result) static::findRelated($_result, $processed);
		elseif(is_object($result)) {
			foreach(array('hasOne' => static::p()->hasOne, 'hasMany' => static::p()->hasMany, 'belongsTo' => static::p()->belongsTo) as $relationType => $relations) {
				foreach($relations as $alias => $relation) {
					// Prevent infinite looping, don't relate this model if it's already been related in this cascading find operation.
					if(in_array($relation['model'], $processed)) continue;
					
					$processed[] = $relation['model'];
					$relation['options']['processed'] = $processed;
					
					if(!isset($relation['options']['conditions'])) $relation['options']['conditions'] = array();
					if($relationType == 'belongsTo') $relation['options']['conditions'][$relation['model']::getPrimaryKey()] = $result->{$relation['foreignKey']};
					else $relation['options']['conditions'][$relation['foreignKey']] = $result->{static::getPrimaryKey()};
					
					if($relationType == 'hasMany') $alias = Inflector::pluralize($alias);
					
					$result->{$alias} = $relation['model']::find($relation['options']);
				}
			}
		}else throw new BadArgumentException('Model::findRelated() expects parameter 1 to be object or array, \'' . gettype($result) . '\' given.');
		
	}
	
	/**
	 * Inserts the data described by $data into the database.  Also updates the primary key with {@link getInsertId()}.  $data should be in the same format as returned by {@link find()} (object of field->value pairs).
	 * 
	 * @param object $data An array or object of data.
	 * @return bool True on success, false on failure.
	 * @since 0.1
	 * @throws BadArgumentException Thrown when $data is not an object.
	 */
	public static function insert(&$data) {
		
		if(!is_object($data)) throw new BadArgumentException("Model::insert() requires an object as input.");
		
		$fields = array();
		$values = array();
		foreach($data as $field => $value) {
			if(!in_array($field, static::getFields())) continue;
			$fields[] = "`$field`";
			$values[] = "'" . Database::escape($value) . "'";
		}
		
		$query = 'insert into `' . static::getTableName() . "` (" . implode(', ', $fields) . ') values (' . implode(', ', $values) . ')';
		if(Database::query($query)) {
			if(Database::getInsertId()) $data->{static::getPrimaryKey()} = Database::getInsertId();
			return true;
		}else return false;
		
	}
	
	/**
	 * Updates a record in the database.
	 * 
	 * Updates the record in database, identified by the primary key in $data, with the other values in $data.
	 * 
	 * @param object $data The updated record.
	 * @return bool True on success, false on failure.
	 * @since 0.1
	 * @throws BadArgumentException Thrown when $data is not an object or does not contain a value for the primary key.
	 */
	public static function update($data) {
		
		if(!is_object($data)) throw new BadArgumentException("Model::update() requires an object as input.");
		if(!isset($data->{static::getPrimaryKey()})) throw new BadArgumentException("Model::update() requires the supplied data to include the primary key of the item to update.");
		
		$primaryKey = $data->{static::getPrimaryKey()};
		$updates = array();
		foreach($data as $field => $value) {
			if(!in_array($field, static::getFields()) or $field == static::getPrimaryKey()) continue;
			$updates[] = "`$field` = '" . Database::escape($value) . "'";
		}
		
		$query = 'update `' . static::getTableName() . '` set ' . implode(', ', $updates) . ' where `' . static::getPrimaryKey() . "` = '$primaryKey' limit 1";
		return Database::query($query);
		
	}
	
	/**
	 * Updates or inserts the given record depending on its content.
	 * 
	 * Either updates or inserts the record described by $data depending on whether or not the primary key is in the data and whether or not it is unique in the table.
	 * 
	 * Options keys are:
	 * - cascade: Whether or not to also save related rows in $data.
	 * - validate: Whether or not to validate the data before saving it.  If validation fails the save will cancel and return false.
	 * 
	 * @param object $data The data to save.
	 * @param array $options Array of options for the save.
	 * @return bool True on success, false on failure.
	 * @since 0.1
	 * @throws BadArgumentException Thrown when $data is not an object or $options is not an array.
	 */
	public function save(&$data, $options = array()) {
		
		if(!is_object($data)) throw new BadArgumentException("Model::save() expects parameter 1 to be an object, '" . gettype($data) . "' given.");
		if(!is_array($options)) throw new BadArgumentException("Model::save() expected parameter 2 to be an array, '" . gettype($options) . "' given.");
		if(!isset($options['cascade'])) $options['cascade'] = false;
		if(!isset($options['validate'])) $options['validate'] = true;
		$return = array();
		
		if($options['cascade']) {
			foreach($this->belongsTo as $alias => $relation) {
				if(!isset($data->{$alias})) continue;
				$return[] = $relation['model']->save($data->{$alias}, $options);
				$data->{$relation['foreignKey']} = $data->{$alias}->{$relation['model']->getPrimaryKey()};
			}
		}
		
		if(!isset($data->{$this->getPrimaryKey()})) $function = 'insert';
		elseif(!$this->{'findFirstBy' . Inflector::camelize($this->getPrimaryKey())}($data->{$this->getPrimaryKey()})) $function = 'insert';
		else $function = 'update';
		if($options['validate']) if($this->validate($data) !== true) return false;
		$return[] = $this->$function($data);
		
		if($options['cascade']) {
			foreach($this->hasMany as $alias => $relation) {
				$alias = Inflector::pluralize($alias);
				if(!isset($data->{$alias})) continue;
				foreach($data->{$alias} as &$_data) {
					$_data->{$relation['foreignKey']} = $data->{$this->getPrimaryKey()};
					$return[] = $relation['model']->save($_data, $options);
				}
			}
			foreach($this->hasOne as $alias => $relation) {
				if(!isset($data->{$alias})) continue;
				$data->{$alias}->{$relation['foreignKey']} = $data->{$this->getPrimaryKey()};
				$return[] = $relation['model']->save($data->{$alias}, $options);
			}
		}
		
		foreach($return as $bool) if(!$bool) return false;
		return true;
		
	}
	
	/**
	 * Deletes the record described by $data from the database.
	 * 
	 * @param mixed $data The record to delete.
	 * @return bool True on success, false on failure.
	 * @since 0.1
	 * @throws BadArgumentException Thrown when $data is not an object or it does not contain a value for the primary key.
	 */
	public function delete($data) {
		
		if(!is_object($data)) throw new BadArgumentException("Model::delete() requires an array or object as input.");
		if(!isset($data->{$this->getPrimaryKey()})) throw new BadArgumentException("Model::delete() requires the supplied data to include the primary key of the item to update.");
		
		$primaryKey = $data->{$this->getPrimaryKey()};
		$query = 'delete from `' . $this->getTableName() .'` where `' . $this->getPrimaryKey() . "` = '$primaryKey' limit 1";
		return Database::query($query);
		
	}
	
	/**
	 * Validates some data with the rules defined in the model.
	 * 
	 * @param mixed $data The data to be validated, either object or array.
	 * @param array $ignore An array of rules to ignore.
	 * @return mixed True if data validates successfully, an array of errors otherwise.
	 * @see $validate
	 * @since 0.2
	 * @throws BadArgumentException Thrown when $data is not an object or $ignore is not an array.
	 * @throws BadConfigurationException Thrown when an invalid rule is encountered in {@link $validate}.
	 */
	public function validate($data, $ignore = array()) {
		
		if(!is_object($data)) throw new BadArgumentException("Model::validate() expects parameter 1 to be an object, " . gettype($data) . ' given.');
		if(!is_array($ignore)) throw new BadArgumentException("Model::validate() expects parameter 2 to be an array, '" . gettype($ignore) . "' given.");
		
		if(empty($this->validate)) return true;
		
		$errors = array();
		foreach($this->validate as $field => $rules) {
			foreach($rules as $rule => $args) {
				if(is_int($rule)) $rule = $args;
				
				if(isset($ignore[$field]) and is_array($ignore[$field]) and in_array($rule, $ignore[$field])) continue;
				if(isset($ignore['all']) and is_array($ignore['all']) and in_array($rule, $ignore['all'])) continue;
				
				if(!isset($data->{$field})) {
					if($rule == 'required') $errors[$field][$rule] = $rule;
					continue;
				}else $value = $data->{$field};
				
				switch($rule) {
					case 'minlength':
						$pass = (strlen(strval($value)) >= $args);
						break;
					case 'maxlength':
						$pass = (strlen(strval($value)) <= $args);
						break;
					case 'length':
						$pass = (strlen(strval($value)) == $args);
						break;
					case 'numeric':
						$pass = is_numeric($value);
					case 'unique':
						$pass = !(bool)$this->{'findFirstBy' . Inflector::camelize($field)}($value);
						break;
					case 'date':
						$pass = !is_bool(strtotime(strval($value)));
						break;
					case 'dateformat':
						$pass = (date($args, strtotime(strval($value))) == $value);
						break;
					case 'regex':
						$pass = (bool)preg_match($args, strval($value));
						break;
					case 'callback':
						if(is_array($args) and isset($args[0]) and is_callable($args[0])) {
							$_args = $args;
							$callback = array_shift($_args);
						}elseif(is_callable($args)) {
							$callback = $args;
							$_args = array();
						}
						$pass = (bool)call_user_func_array($callback, array($value) + $_args);
						break;
					case 'required':
						$pass = !empty($value);
						break;
					default:
						throw new BadConfigurationException("Invalid validation rule '$rule' defined in model " . $this->getName());
						break;
				}
				if(!$pass) $errors[$field][$rule] = $args;
			}
		}
		
		return empty($errors) ? true : $errors;
		
	}
	
}

/**
 * Bad configuration exception.
 * 
 * An exception thrown when a model contains bad configuration such as validation or relations.
 * 
 * @subpackage exceptions
 * @version 0.1
 */
class BadConfigurationException extends MVCException {}

?>