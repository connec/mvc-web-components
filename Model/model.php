<?php

/**
 * Contains the Model class and related classes.
 *
 * @package mvc-web-components.components
 * @author Chris Connelly
 */
namespace MVCWebComponents\Model;
use MVCWebComponents\MVCException,
	MVCWebComponents\BadArgumentException,
	MVCWebComponents\Inflector,
	MVCWebComponents\Database\Database,
	MVCWebComponents\ExtensibleStatic;

/**
 * The Model class is an extensible class to allow 'zero configuration' CRUD + extras database interaction.
 * 
 * Once a table has been created in the database all that is needed to allow CRUD functionality is:
 * <code>
 * class User extends Model {}
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
 * @version 0.9.4
 */
abstract class Model extends ExtensibleStatic {
	
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
	 * Describes 'belongs to' relationships.
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
	 * An array of method names to execute before record construction.
	 * 
	 * @var array
	 * @since 0.9.1
	 */
	protected static $beforeConstruct = array();
	
	/**
	 * An array of method names to execute after record construction.
	 * 
	 * @var array
	 * @since 0.9.1
	 */
	protected static $afterConstruct = array();
	
	/**
	 * An array of method names to execute before saving a record.
	 * 
	 * @var array
	 * @since 0.9.1
	 */
	protected static $beforeSave = array();
	
	/**
	 * An array of method names to execute after saving a record.
	 * 
	 * @var array
	 * @since 0.9.1
	 */
	protected static $afterSave = array();
	
	/**
	 * A hash of field values for this record.
	 * 
	 * @var array
	 * @since 0.9
	 */
	protected $fields = array();
	
	/**
	 * A hash of related records.
	 * 
	 * @var array
	 * @since 0.9
	 */
	protected $related = array();
	
	/**
	 * A flag representing whether this record has been modified.
	 * 
	 * @var bool
	 * @since 0.9.4
	 */
	public $touched = true;
	
	/**
	 * An array of validation errors for this record.
	 * @var array
	 * @since 0.9
	 */
	public $errors = array();
	
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
	 * Returns the number of rows in the table, as reported by the Table instance.
	 * 
	 * @return int
	 * @since 0.9.2
	 * @see Table
	 */
	public static function getRowCount() {
		
		static::__init();
		return static::properties()->table->getRowCount();
		
	}
	
	/**
	 * Returns the fields of this record as a hash.
	 * 
	 * @return array A hash of fields and values for this model.
	 * @since 0.9
	 */
	public function getArray() {
		
		$return = $this->fields;
		foreach($this->related as $field => $related) {
			if(is_array($related)) {
				$return[$field] = array();
				foreach($related as $record) {
					$return[$field][] = $record->getArray();
				}
			}else $return[$field] = $related->getArray();
		}
		return $return;
		
	}
	
	/**
	 * Runs any hooks named under $name.
	 * 
	 * @param string $name The name of the hook to run.
	 * @param bool $required If set to true an exception will be raised if no methods are defined under $name.
	 * @return bool The reduced value of the return values of the hook methods.  Returns true if all hooks return true, false otherwise.
	 * @throws BadConfigurationException Thrown when a non-existant method is encountered.
	 * @throws BadArgumentException Thrown when the provided $name is not a valid hook.
	 * @since 0.9.1
	 */
	public function runHook($name, $required = false) {
		
		if(!isset(static::$$name)) throw new BadArgumentException("Invalid hook name '$name' given to " . static::getName() . "::runHook(), see documentation for valid hooks.");
		if(!empty(static::$$name)) {
			$return = array();
			foreach(static::$$name as $method) {
				if(method_exists($this, $method)) $return[] = $this->$method();
				else throw new BadConfigurationException("Model '" . static::getName() . "' contains a bad method '$method' for hook '$name'.  Ensure the method exists.");
			}
			return array_reduce($return, function($a, $b) {return $a and $b;}, true);
		}
		if($required) throw new MVCException("No methods found for required hook '$name' in " . static::getName() . ".");
		else return true;
		
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
	 * Creates an instance of the model representing a record in the table.
	 * 
	 * @param array $fields   A hash of fields and values to give the new record.  If no value is given for a field the schema default is used.
	 * @param bool  $fromFind Whether this record was instantiated by a find operation.
	 * @return void
	 * @since 0.9
	 */
	public function __construct($fields = array(), $fromFind = false) {
		
		static::__init();
		$this->fields = static::p()->table->getDefaultRecord();
		
		$this->runHook('beforeConstruct');
		
		foreach(static::getFields() as $field)
			if(isset($fields[$field])) $this->fields[$field] = $fields[$field];
		if($fromFind) $this->touched = false;
		
		$this->runHook('afterConstruct');
		
	}
	
	/**
	 * Allows accessing of record fields.
	 * 
	 * @param string $field The name of the field to return.
	 * @return mixed The value of $field.
	 * @since 0.9
	 * @throws InvalidFieldException Thrown when attempting to access or modify a non-existant field or relation.
	 */
	public function &__get($field) {
		
		// Avoids errors about variable references.
		$null = null;
		
		// Return the actual primary key value if the request is for primary_key.
		if($field == 'primary_key') return $this->fields[static::getPrimaryKey()];
		
		// Check if it's a field first.
		if(isset($this->fields[$field])) return $this->fields[$field];
		if(in_array($field, static::getFields())) return $null;
		
		// Check relations...
		if(isset(static::p()->hasOne[$field]) or isset(static::p()->hasMany[Inflector::singularize($field)]) or isset(static::p()->belongsTo[$field])) {
			if(isset($this->related[$field])) return $this->related[$field];
			else return $null;
		}else throw new InvalidFieldException($field, static::p()->name);
		
	}
	
	/**
	 * Allows setting of record fields.
	 * 
	 * @param string $field The name of the field to set.
	 * @param mixed $value The value to assign.
	 * @return void
	 * @since 0.9
	 * @throws InvalidFieldException Thrown when attempting to access or modify a non-existant field or relation.
	 */
	public function __set($field, $value) {
		
		$this->touched = true;
		
		// First check if it's setting the primary_key...
		if($field == 'primary_key') $this->fields[static::getPrimaryKey()] = $value;
		
		// Check fields first...
		elseif(in_array($field, static::getFields())) $this->fields[$field] = $value;
		
		// Check the related models...
		elseif(isset(static::p()->hasOne[$field]) or isset(static::p()->hasMany[Inflector::singularize($field)]) or isset(static::p()->belongsTo[$field])) $this->related[$field] = $value;
		else {
			$this->touched = false;
			throw new InvalidFieldException($field, static::p()->name);
		}
		
	}
	
	/**
	 * Extends isset() to include fields and relations.
	 * 
	 * @param string $field The field to check.
	 * @return bool True if $field is a field or relation and isn't null, false otherwise.
	 * @since 0.9
	 */
	public function __isset($field) {
		
		if($field == 'primary_key') return isset($this->fields[static::getPrimaryKey()]);
		return isset($this->fields[$field]) ? true : (isset($this->related[$field]) ? true : false);
		
	}
	
	/**
	 * Extends unset() to include fields and relations.
	 * 
	 * @param string $field The field to unset.
	 * @return void
	 * @since 0.9
	 * @throws InvalidFieldException Thrown when attempting to access or modify a non-existant field or relation.
	 */
	public function __unset($field) {
		
		$this->touched = true;
		
		if($field == 'primary_key') unset($this->fields[static::getPrimaryKey()]);
		elseif(in_array($field, static::getFields())) unset($this->fields[$field]);
		elseif(isset($this->related[$field])) unset($this->related[$field]);
		else {
			$this->touched = false;
			throw new InvalidFieldException($field, static::p()->name);
		}
		
	}
	
	/**
	 * Sets up the model for use.
	 * 
	 * @return void
	 * @since 0.8
	 */
	public static function __init() {
		
		if(!isset(static::$states[get_called_class()])) static::$states[get_called_class()] = new \StdClass;
		else return;
		
		// Store the model name (sans namespace) in the metadata.
		static::p()->name = @end(explode('\\', static::getName()));
		static::p()->tableName = static::$tableName ?: Inflector::tableize(static::p()->name);
		static::p()->table = Table::instance(static::p()->tableName, static::getName());
		static::p()->validate = static::$validate ?: array();
		
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
	 * </code>
	 * 
	 * @return void
	 * @since 0.8
	 * @throws BadConfigurationException Thrown when an invalid relationship configuration is found.
	 */
	protected static function normalizeRelations() {
		
		static::p()->hasOne = static::$hasOne;
		static::p()->hasMany = static::$hasMany;
		static::p()->belongsTo = static::$belongsTo;
		
		$relationConfig = array('hasOne' => &static::p()->hasOne, 'hasMany' => &static::p()->hasMany, 'belongsTo' => &static::p()->belongsTo);
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
				if(!class_exists($relation['model'], false)) {
					// Try prefixing the namespace of the current model.
					$namespace = str_replace(static::p()->name, '', get_called_class());
					$relation['model'] = "$namespace$model";
					
					// Run a method with that class, if it doesn't exist the user will have to sort it out.
					$relation['model']::getName();
				}
				
				$key = Inflector::underscore(static::p()->name) . '_' . static::getPrimaryKey();
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
			$args[0] = array_merge($args[0], $options);
			return call_user_func_array(array(static::getName(), 'find'), $args);
		}
		
		if(array_shift($methodParts) != 'by') $error();
		if(empty($methodParts)) $error();
		
		$field = implode('_', $methodParts);
		if(!in_array($field, static::getFields())) $error();
		$options['conditions'] = array($field => array_shift($args));
		
		if(!isset($args[0]) or !is_array($args[0])) $args[0] = array();
		$args[0] = array_merge_recursive($args[0], $options);
		
		return call_user_func_array(array(static::getName(), 'find'), $args);
		
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
	public static function find($options = array(), $cascade = true, $processed = array()) {
		
		static::__init();
		
		// Initialize the processed array.
		if(empty($processed)) $processed = array(static::getName());
		
		// Query the table.
		$return = static::p()->table->find($options);
		
		// Relate the result if in the options.
		if($return and $cascade) static::findRelated($return, $processed);
		
		// Return it.
		return $return;
		
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
					
					if(!isset($relation['options']['conditions'])) $relation['options']['conditions'] = array();
					if($relationType == 'belongsTo') $relation['options']['conditions'][$relation['model']::getPrimaryKey()] = $result->{$relation['foreignKey']};
					else $relation['options']['conditions'][$relation['foreignKey']] = $result->{static::getPrimaryKey()};
					
					if($relationType == 'hasMany') $alias = Inflector::pluralize($alias);
					
					$result->$alias = $relation['model']::find($relation['options'], true, $processed);
				}
			}
		}else throw new BadArgumentException('Model::findRelated() expects parameter 1 to be object or array, \'' . gettype($result) . '\' given.');
		
	}
	
	/**
	 * Updates or inserts this record depending on its content.
	 * 
	 * Either updates or inserts this record depending on whether or not the primary key is in the data and whether or not it is unique in the table.
	 * 
	 * Options keys are:
	 * - cascade: Whether or not to also save related rows.
	 * - validate: Whether or not to validate the data before saving it.  If validation fails the save will cancel and return false.
	 * 
	 * @param array $options Array of options for the save.
	 * @return bool True on success, false on failure.
	 * @since 0.1
	 * @throws BadArgumentException Thrown when $data is not an object or $options is not an array.
	 */
	public function save($options = array()) {
		
		if(!is_array($options)) throw new BadArgumentException("Model::save() expected parameter 1 to be an array, '" . gettype($options) . "' given.");
		if(!isset($options['cascade'])) $options['cascade'] = false;
		if(!isset($options['validate'])) $options['validate'] = true;
		$return = array();
		
		$this->runHook('beforeSave');
		
		if($options['cascade']) {
			foreach(static::p()->belongsTo as $alias => $relation) {
				if(!isset($this->$alias)) continue;
				$return[] = $this->$alias->save($options);
				$this->{$relation['foreignKey']} = $this->$alias->primary_key;
			}
		}
		
		$idFunction = 'findFirstBy' . Inflector::camelize(static::getPrimaryKey());
		if(!isset($this->primary_key)) $function = 'insert';
		elseif(!static::$idFunction($this->primary_key)) $function = 'insert';
		else $function = 'update';
		
		if($options['validate']) if($this->validate() !== true) return false;
		if($function == 'update') $return[] = static::p()->table->update($this);
		else {
			if(static::p()->table->insert($this)) $return[] = true;
			else $return[] = false;
		}
		
		if($options['cascade']) {
			foreach(static::p()->hasMany as $alias => $relation) {
				$alias = Inflector::pluralize($alias);
				if(!isset($this->$alias)) continue;
				foreach($this->$alias as &$data) {
					$data->{$relation['foreignKey']} = $this->primary_key;
					$return[] = $data->save($options);
				}
			}
			foreach(static::p()->hasOne as $alias => $relation) {
				if(!isset($this->$alias)) continue;
				$this->$alias->{$relation['foreignKey']} = $this->primary_key;
				$return[] = $this->$alias->save($options);
			}
		}
		
		$this->runHook('afterSave');
		
		return array_reduce($return, function($a,$b){return $a and $b;}, true);
		
	}
	
	/**
	 * Deletes this record from the database.
	 * 
	 * @return bool True on success, false on failure.
	 * @since 0.1
	 * @throws BadArgumentException Thrown when $data is not an object or it does not contain a value for the primary key.
	 */
	public function delete() {
		
		return static::p()->table->delete($this);
		
	}
	
	/**
	 * Validates some data with the rules defined in the model.
	 * 
	 * @param array $ignore An array of rules to ignore.
	 * @return mixed True if data validates successfully, an array of errors otherwise.
	 * @see $validate
	 * @since 0.2
	 * @throws BadArgumentException Thrown when $ignore is not an array.
	 * @throws BadConfigurationException Thrown when an invalid rule is encountered in {@link $validate}.
	 */
	public function validate($ignore = array()) {
		
		if(!is_array($ignore)) throw new BadArgumentException("Model::validate() expects parameter 1 to be an array, '" . gettype($ignore) . "' given.");
		
		if(empty(static::p()->validate)) return true;
		
		$errors = array();
		foreach(static::p()->validate as $field => $rules) {
			foreach($rules as $rule => $args) {
				if(is_int($rule)) $rule = $args;
				
				if(isset($ignore[$field]) and is_array($ignore[$field]) and in_array($rule, $ignore[$field])) continue;
				if(isset($ignore['all']) and is_array($ignore['all']) and in_array($rule, $ignore['all'])) continue;
				
				if(!isset($this->$field)) {
					if($rule == 'required') $errors[$field][$rule] = $rule;
					continue;
				}else $value = $this->$field;
				
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
						break;
					case 'unique':
						$function = 'findFirstBy' . Inflector::camelize($field);
						$pass = !(bool)static::$function($value);
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
						}else throw new BadConfigurationException("Invalid callback defined in model " . static::getName());
						$pass = (bool)call_user_func_array($callback, array($value) + $_args);
						break;
					case 'required':
						$pass = !empty($value);
						break;
					default:
						throw new BadConfigurationException("Invalid validation rule '$rule' defined in model " . static::getName());
						break;
				}
				if(!$pass) $errors[$field][$rule] = $args;
			}
		}
		
		$this->errors = $errors;
		return empty($errors);
		
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

/**
 * An exception thrown when attempting to set or get an invalid field.
 * 
 * @subpackage exceptions
 * @version 1.0
 */
class InvalidFieldException extends MVCException {
	
	/**
	 * Set the message.
	 * 
	 * @param string $field The invalid field name.
	 * @return void
	 * @since 1.0
	 */
	public function __construct($field, $model) {
		
		$this->message = "Attempted to access invalid field '$field' for model '$model'.";
		
	}
	
}

?>