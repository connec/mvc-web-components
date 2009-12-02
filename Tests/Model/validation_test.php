<?php

include 'common.php';
use MVCWebComponents\Model\Model as Model, MVCWebComponents\UnitTest as UnitTest;

function is_multiple_of_five($test) {
	return ($test % 5 == 0);
}

class User extends Model {
	
	protected $validate = array(
		'id' => array('unique', 'numeric'),
		'name' => array('maxlength' => 32, 'unique', 'required'),
		'password' => array('maxlength' => 64, 'minlength' => 6, 'required'),
		'user_group_id' => array('numeric'),
		'date' => array('dateformat' => 'Y-m-d H:i:s'),
		'five' => array('callback' => 'is_multiple_of_five')
	);
	
}

class ValidationTest extends UnitTest {
	
	public function preTesting() {
		
		return $this->user = User::getInstance();
		
	}
	
	// Test failing the minlength condition, and test ignoring it.
	public function TestIgnoreAndFailMinLength() {
		
		$user =(object)array(
			'name' => 'Jimminy Billybob',
			'password' => '1');
		
		$this->assertEqual($this->user->validate($user), array('password' => array('minlength' => 6)));
		$this->assertTrue($this->user->validate($user, array('all' => array('minlength'))));
		
	}
	
	// Test failing the unique and required conditions
	public function TestFailUniqueAndRequire() {
		
		$user = (object)array(
			'name' => 'Bob');
		
		$this->assertEqual($this->user->validate($user), array('name' => array('unique' => 'unique'), 'password' => array('required' => 'required')));
		
	}
	
	// Test passing maxlength, unique, required, minlength and dateformat
	public function TestPassMaxLenMinLenUniqueRequiredDateFormat() {
		
		$user = (object)array(
			'name' => 'Hello World',
			'password' => 'Password',
			'date' => date('Y-m-d H:i:s'));
		
		$this->assertTrue($this->user->validate($user));
		
	}
	
	// Test passing callback
	public function TestPassingCallback() {
		
		$user = (object)array(
			'name' => 'Not Taken',
			'password' => 'Passwizzle',
			'five' => 10);
		
		$this->assertTrue($this->user->validate($user));
		
	}
	
}

new ValidationTest;

?>