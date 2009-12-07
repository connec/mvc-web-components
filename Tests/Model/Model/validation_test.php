<?php

namespace ValidationTest;
use MVCWebComponents\Model\Model, MVCWebComponents\UnitTest\UnitTest;

function is_multiple_of_5($x) { return $x % 5 ==0; }

class User extends Model {
	
	protected static $validate = array(
		'id' => array('unique'),
		'name' => array(
			'unique',
			'required',
			'minlength' => 3,
			'maxlength' => 32),
		'password' => array(
			'required',
			'minlength' => 6,
			'maxlength' => 64),
		'user_group_id' => array('length' => 1),
		'joined' => array('length' => 10),
		'date' => array('dateformat' => 'Y-m-d H:i:s'),
		'email' => array('regex' => '|[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}|i'),
		'five' => array('callback' => 'ValidationTest\\is_multiple_of_5'));
	
}

class User2 extends Model {
	
	protected static $tableName = 'users';
	
	protected static $validate = array(
		'joined' => array('dateformat' => 'Y-m-d H:i:s'),
		'name' => array('regex' => '|[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}|i'),
		'password' => array('callback' => 'ValidationTest\\is_multiple_of_5'));
	
}

class ValidationTest extends UnitTest {
	
	public $dependencies = array('InitTest');
	
	public function TestLengthConditions() {
		
		// Test passing.
		$user = new User(array(
			'name' => 'George',
			'password' => 'passwizzle',
			'joined' => time()));
		$this->assertTrue($user->validate());
		
		// Test failing.
		$user = new User(array(
			'name' => '',
			'password' => str_repeat(' ', 65),
			'joined' => 0));
		$this->assertFalse($user->validate());
		$this->assertStrict($user->errors, array(
			'name' => array(
				'required' => 'required',
				'minlength' => 3),
			'password' => array(
				'maxlength' => 64),
			'joined' => array(
				'length' => 10)));
		
		$user = new User(array('name' => 'Fred'));
		$this->assertFalse($user->validate());
		$this->assertStrict($user->errors, array('password' => array('required' => 'required')));
		
	}
	
	public function TestUnique() {
		
		$user = new User(array('name' => 'Fred', 'password' => 'password'));
		$this->assertTrue($user->validate());
		
		$user->name = 'Bob';
		$this->assertFalse($user->validate());
		$this->assertStrict($user->errors, array('name' => array('unique' => 'unique')));
		
	}
	
	public function TestDateFormat() {
		
		$user = new User2(array('joined' => date('Y-m-d H:i:s')));
		$this->assertTrue($user->validate());
		
		$user->joined = date('d/m/y H:i:s');
		$this->assertFalse($user->validate());
		$this->assertStrict($user->errors, array('joined' => array('dateformat' => 'Y-m-d H:i:s')));
		
	}
	
	public function TestRegex() {
		
		$user = new User2(array('name' => 'connec.2002@gmail.com'));
		$this->assertTrue($user->validate());
		
		$user->name = 'wrong';
		$this->assertFalse($user->validate());
		$this->assertStrict($user->errors, array('name' => array('regex' => '|[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}|i')));
		
	}
	
	public function TestCallback() {
		
		$user = new User2(array('password' => 10));
		$this->assertTrue($user->validate());
		
		$user->password = 7;
		$this->assertFalse($user->validate());
		$this->assertStrict($user->errors, array('password' => array('callback' => 'ValidationTest\\is_multiple_of_5')));
		
	}
	
}

?>