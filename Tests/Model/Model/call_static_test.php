<?php

namespace CallStaticTest;
use MVCWebComponents\Model\Model, MVCWebComponents\UnitTest\UnitTest;

class User extends Model {}

class CallStaticTest extends UnitTest {
	
	// This test assumes InitTest and FindTest pass.
	public $dependencies = array('InitTest', 'FindTest');
	
	public function TestFindAll() {
		
		$this->assertEqual(User::findAll(array(), false), array(
			new User(array(
				'id' => 1,
				'name' => 'Bob',
				'password' => 'bobsnewpass',
				'user_group_id' => 1,
				'joined' => 1251141357)),
			new User(array(
				'id' => 2,
				'name' => 'Jim',
				'password' => 'jimspass',
				'user_group_id' => 1,
				'joined' => 1251141357))
		));
		
	}
	
	public function TestFindFirst() {
		
		$this->assertEqual(User::findFirst(array(), false), new User(array(
			'id' => 1,
			'name' => 'Bob',
			'password' => 'bobsnewpass',
			'user_group_id' => 1,
			'joined' => 1251141357
		)));
		
	}
	
	public function TestFindAllBy() {
		
		$this->assertEqual(User::findAllById(2, array(), false), array(
			new User(array(
				'id' => 2,
				'name' => 'Jim',
				'password' => 'jimspass',
				'user_group_id' => 1,
				'joined' => 1251141357))
		));
		
	}
	
	public function TestFindFirstBy() {
		
		$this->assertEqual(User::findFirstByName('Jim', array(), false), new User(array(
			'id' => 2,
			'name' => 'Jim',
			'password' => 'jimspass',
			'user_group_id' => 1,
			'joined' => 1251141357
		)));
		
		// Test a longer-than-one-word field
		$this->assertEqual(User::findFirstByUserGroupId(1, array(), false), new User(array(
			'id' => 1,
			'name' => 'Bob',
			'password' => 'bobsnewpass',
			'user_group_id' => 1,
			'joined' => 1251141357
		)));
		
	}
	
}

?>