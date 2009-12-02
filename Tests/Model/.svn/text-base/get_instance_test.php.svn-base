<?php

include_once 'common.php';
use MVCWebComponents\Model\Model, MVCWebComponents\UnitTest;

class User extends Model {}

class GetInstanceTest extends UnitTest {
	
	// Test the getInstance method returns a reference to a model with that alias.
	public function TestReference() {
		
		$this->assertStrict(User::getInstance(), User::getInstance());
		$this->assertStrict(User::getInstance('Alias'), User::getInstance('Alias'));
		$this->assertFalse(User::getInstance() === User::getInstance('Alias'));
		
	}
	
	// Test the default alias is assigned correctly.
	public function TestDefaultAlias() {
		
		$user = User::getInstance();
		
		$this->assertStrict($user->getAlias(), 'User');
		
	}
	
	// Test aliases can be assigned.
	public function TestAssignAlias() {
		
		$user = User::getInstance('Other');
		
		$this->assertStrict($user->getAlias(), 'Other');
		
	}
	
}

new GetInstanceTest;

?>