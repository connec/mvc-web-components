<?php

namespace InitTest;
use MVCWebComponents\Model\Model, MVCWebComponents\Model\Table, MVCWebComponents\UnitTest;

class User extends Model {
	
	protected static $hasOne = array('Post', 'Signature');
	protected static $hasMany = array('Post', 'SomethingElse');
	protected static $belongsTo = array(
		'Parent' => array(
			'model' => '\\InitTest\\User'
		)
	);
	
}

class InitTest extends UnitTest {
	
	// Ensure the table initlializes correctly.
	public function TestTableInit() {
		
		// Check the table is loaded.
		$table = Table::getInstance('users');
		$this->assertEqual(User::getTableName(), 'users');
		$this->assertEqual(User::getPrimaryKey(), $table->getPrimaryKey());
		$this->assertEqual(User::getFields(), $table->getFields());
		
	}
	
	// Ensure the relations are normalized.
	public function TestRelationNormalisation() {
		
		// Check the relations are normalized.
		$user = User::dump(false); // Get the info via dump()
		$this->assertEqual($user->hasOne, array(
			'Post' => array(
				'model' => 'Post',
				'foreignKey' => 'user_id',
				'options' => array('type' => 'first', 'limit' => 1)),
			'Signature' => array(
				'model' => 'Signature',
				'foreignKey' => 'user_id',
				'options' => array('type' => 'first', 'limit' => 1))
		));
		$this->assertEqual($user->hasMany, array(
			'Post' => array(
				'model' => 'Post',
				'foreignKey' => 'user_id',
				'options' => array()),
			'SomethingElse' => array(
				'model' => 'SomethingElse',
				'foreignKey' => 'user_id',
				'options' => array())
		));
		$this->assertEqual($user->belongsTo, array(
			'Parent' => array(
				'model' => '\\InitTest\\User',
				'foreignKey' => 'parent_id',
				'options' => array('type' => 'first', 'limit' => 1))
		));
		
	}
	
}

?>