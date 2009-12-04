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

class ValidationTest extends UnitTest {
	
	public $dependencies = array('InitTest');
	
	public function TestLengthConditions() {
		
		// Test passing.
		$user = (object)array(
			'name' => 'George',
			'password' => 'passwizzle',
			'joined' => time());
		$this->assertTrue(User::validate($user));
		
		// Test failing.
		$user = (object)array(
			'name' => '',
			'password' => str_repeat(' ', 65),
			'joined' => 0);
		$this->assertFalse(User::validate($user));
		$this->assertStrict(User::getErrors(), array(
			'name' => array(
				'required' => 'required',
				'minlength' => 3),
			'password' => array(
				'maxlength' => 64),
			'joined' => array(
				'length' => 10)));
		
		$user = (object)array('name' => 'Fred');
		$this->assertFalse(User::validate($user));
		$this->assertStrict(User::getErrors(), array('password' => array('required' => 'required')));
		
	}
	
	public function TestUnique() {
		
		$user = (object)array('name' => 'Fred', 'password' => 'password');
		$this->assertTrue(User::validate($user));
		
		$user->name = 'Bob';
		$this->assertFalse(User::validate($user));
		$this->assertStrict(User::getErrors(), array('name' => array('unique' => 'unique')));
		
	}
	
	public function TestDateFormat() {
		
		$user = (object)array('name' => 'Fred', 'password' => 'password', 'date' => date('Y-m-d H:i:s'));
		$this->assertTrue(User::validate($user));
		
		$user->date = date('d/m/y H:i:s');
		$this->assertFalse(User::validate($user));
		$this->assertStrict(User::getErrors(), array('date' => array('dateformat' => 'Y-m-d H:i:s')));
		
	}
	
	public function TestRegex() {
		
		$user = (object)array('name' => 'Fred', 'password' => 'password', 'email' => 'connec.2002@gmail.com');
		$this->assertTrue(User::validate($user));
		
		$user->email = 'wrong';
		$this->assertFalse(User::validate($user));
		$this->assertStrict(User::getErrors(), array('email' => array('regex' => '|[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}|i')));
		
	}
	
	public function TestCallback() {
		
		$user = (object)array('name' => 'Fred', 'password' => 'password', 'five' => 10);
		$this->assertTrue(User::validate($user));
		
		$user->five = 7;
		$this->assertFalse(User::validate($user));
		$this->assertStrict(User::getErrors(), array('five' => array('callback' => 'ValidationTest\\is_multiple_of_5')));
		
	}
	
}

?>