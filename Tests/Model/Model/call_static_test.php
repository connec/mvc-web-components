<?php

namespace CallStaticTest;
use MVCWebComponents\Model\Model, MVCWebComponents\UnitTest\UnitTest;

class User extends Model {}

class CallStaticTest extends UnitTest {
	
	// This test assumes InitTest and FindTest pass.
	public $dependencies = array('InitTest', 'FindTest');
	
	public function TestFindAll() {
		
		list($bob,$jim) = User::findAll(array(), false);
		$bob->id = 1; // Force the 'touched' flag to change.
		$jim->id = 2;
		$this->assertEqual(array($bob, $jim), array(
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
		
		$bob = User::findFirst(array(), false);
		$bob->id = 1;
		$this->assertEqual($bob, new User(array(
			'id' => 1,
			'name' => 'Bob',
			'password' => 'bobsnewpass',
			'user_group_id' => 1,
			'joined' => 1251141357
		)));
		
	}
	
	public function TestFindAllBy() {
		
		list($jim) = User::findAllById(2, array(), false);
		$jim->id = 2;
		$this->assertEqual(array($jim), array(
			new User(array(
				'id' => 2,
				'name' => 'Jim',
				'password' => 'jimspass',
				'user_group_id' => 1,
				'joined' => 1251141357))
		));
		
	}
	
	public function TestFindFirstBy() {
		
		$jim = User::findFirstByName('Jim', array(), false);
		$jim->id = 2;
		$this->assertEqual($jim, new User(array(
			'id' => 2,
			'name' => 'Jim',
			'password' => 'jimspass',
			'user_group_id' => 1,
			'joined' => 1251141357
		)));
		
		// Test a longer-than-one-word field
		$bob = User::findFirstByUserGroupId(1, array(), false);
		$bob->id = 1;
		$this->assertEqual($bob, new User(array(
			'id' => 1,
			'name' => 'Bob',
			'password' => 'bobsnewpass',
			'user_group_id' => 1,
			'joined' => 1251141357
		)));
		
	}
	
}

?>