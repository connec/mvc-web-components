<?php

namespace FindTest;
use MVCWebComponents\Model\Model, MVCWebComponents\UnitTest\UnitTest;

class User extends Model {}

class FindTest extends UnitTest {
	
	// This test assumes InitTest passes.
	public $dependencies = array('InitTest');
	
	public function TestDefaults() {
		
		$this->assertEqual(User::find(), array(
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
	
	// Test with setting all options
	public function TestAllOptions() {
		
		$options = array(
			'conditions' => array(),
			'fields' => '*',
			'type' => 'all',
			'orderBy' => '',
			'limit' => 0,
			'operator' => 'and',
			'cascade' => false
		);
		
		// The default options should find all records.
		$this->assertEqual(User::find($options), array(
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
	
	// Test restricting the fields.
	public function TestRestrictFields() {
		
		// Test with fields name and password
		$options = array('fields' => array('name', 'password'), 'cascade' => false);
		$this->assertEqual(User::find($options), array(
			new User(array(
				'name' => 'Bob',
				'password' => 'bobsnewpass')),
			new User(array(
				'name' => 'Jim',
				'password' => 'jimspass'))
		));
		
		// Test with a single field...
		$options = array('fields' => array('id'), 'cascade' => false);
		$this->assertEqual(User::find($options), array(
			new User(array('id' => 1)),
			new User(array('id' => 2))
		));
		
	}
	
	// Test finding a single entry
	public function TestFindFirst() {
		
		$options = array('cascade' => false, 'type' => 'first');
		$this->assertEqual(User::find($options), new User(array(
			'id' => 1,
			'name' => 'Bob',
			'password' => 'bobsnewpass',
			'user_group_id' => 1,
			'joined' => 1251141357
		)));
		
	}
	
	// Test order by
	public function TestOrderBy() {
		
		$options = array('cascade' => false, 'orderBy' => 'id desc', 'type' => 'first', 'fields' => array('id'));
		$this->assertEqual(User::find($options), new User(array('id' => 2)));
		
		$options['orderBy'] = 'id asc';
		$this->assertEqual(User::find($options), new User(array('id' => 1)));
		
		$options['orderBy'] = 'id';
		$this->assertEqual(User::find($options), new User(array('id' => 1)));
		
	}
	
	// Test limit.
	public function TestLimit() {
		
		$options = array('cascade' => false, 'fields' => array('id'), 'limit' => 1);
		$this->assertEqual(User::find($options), array(new User(array('id' => 1))));
		
		$options['limit'] = '1, 1';
		$this->assertEqual(User::find($options), array(new User(array('id' => 2))));
		
	}
	
	// Test conditions.
	public function TestConditions() {
		
		$options = array('cascade' => false, 'fields' => array('id'), 'conditions' => array());
		
		// Start by testing a single condition.
		$options['conditions'] = array('name' => 'Bob');
		$this->assertEqual(User::find($options), array(new User(array('id' => 1))));
		
		// A multi-condition with a condition operator.
		$options['conditions'] = array('name' => '!= Bob', 'id' => '> 1');
		$this->assertEqual(User::find($options), array(new User(array('id' => 2))));
		
		// A failing condition.
		$options['conditions'] = array('name' => 'Bob', 'id' => 2);
		$this->assertStrict(User::find($options), array());
		
		// Again, but for a 'find first'
		$options['type'] = 'first';
		$this->assertStrict(User::find($options), null);
		
		//Finally, a working condition by changing the 'operator'
		$options['operator'] = 'or';
		$options['type'] = 'all';
		$options['orderBy'] = 'id desc';
		$this->assertEqual(User::find($options), array(new User(array('id' => 2)), new User(array('id' => 1))));
		
	}
	
}

?>