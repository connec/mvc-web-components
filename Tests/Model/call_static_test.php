<?php

namespace CallStaticTest;
use MVCWebComponents\Model\Model, MVCWebComponents\UnitTest;

class User extends Model {}

class CallStaticTest extends UnitTest {
	
	// This test assumes InitTest and FindTest pass.
	public $dependencies = array('InitTest', 'FindTest');
	
	public function TestFindAll() {
		
		$this->assertEqual(User::findAll(array('cascade' => false)), array(
			(object)array(
				'id' => 1,
				'name' => 'Bob',
				'password' => 'bobsnewpass',
				'user_group_id' => 1,
				'joined' => 1251141357),
			(object)array(
				'id' => 2,
				'name' => 'Jim',
				'password' => 'jimspass',
				'user_group_id' => 1,
				'joined' => 1251141357)
		));
		
	}
	
	public function TestFindFirst() {
		
		$this->assertEqual(User::findFirst(array('cascade' => false)), (object)array(
			'id' => 1,
			'name' => 'Bob',
			'password' => 'bobsnewpass',
			'user_group_id' => 1,
			'joined' => 1251141357
		));
		
	}
	
	public function TestFindAllBy() {
		
		$this->assertEqual(User::findAllById(2, array('cascade' => false)), array(
			(object)array(
				'id' => 2,
				'name' => 'Jim',
				'password' => 'jimspass',
				'user_group_id' => 1,
				'joined' => 1251141357)
		));
		
	}
	
	public function TestFindFirstBy() {
		
		$this->assertEqual(User::findFirstByName('Jim', array('cascade' => false)), (object)array(
			'id' => 2,
			'name' => 'Jim',
			'password' => 'jimspass',
			'user_group_id' => 1,
			'joined' => 1251141357
		));
		
		// Test a longer-than-one-word field
		$this->assertEqual(User::findFirstByUserGroupId(1, array('cascade' => false)), (object)array(
			'id' => 1,
			'name' => 'Bob',
			'password' => 'bobsnewpass',
			'user_group_id' => 1,
			'joined' => 1251141357
		));
		
	}
	
}

?>