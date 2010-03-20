<?php

namespace FindTest;
use MVCWebComponents\Model\Model, MVCWebComponents\UnitTest\UnitTest;

class User extends Model {}

class FindTest extends UnitTest {
	
	// This test assumes InitTest passes.
	public $dependencies = array('InitTest');
	
	public function preTesting() {
		
		// Store the default users for comparison.
		$this->bob = new User(array(
			'id' => 1,
			'name' => 'Bob',
			'password' => 'bobsnewpass',
			'user_group_id' => 1,
			'joined' => 1251141357));
		
		$this->jim = new User(array(
			'id' => 2,
			'name' => 'Jim',
			'password' => 'jimspass',
			'user_group_id' => 1,
			'joined' => 1251141357));
		
	}
	
	public function TestDefaults() {
		
		$this->assertEqual(User::find(array(), false), array($this->bob, $this->jim));
		
	}
	
	// Test with setting all options
	public function TestAllOptions() {
		
		$options = array(
			'conditions' => array(),
			'type' => 'all',
			'orderBy' => '',
			'limit' => 0,
			'operator' => 'and'
		);
		
		// These options should find all records.
		$this->assertEqual(User::find($options, false), array($this->bob, $this->jim));
		
	}
	
	// Test finding a single entry
	public function TestFindFirst() {
		
		$options = array('type' => 'first');
		$this->assertEqual(User::find($options, false), $this->bob);
		
	}
	
	// Test order by
	public function TestOrderBy() {
		
		$options = array('orderBy' => 'id desc', 'type' => 'first');
		$this->assertEqual(User::find($options, false), $this->jim);
		
		$options['orderBy'] = 'id asc';
		$this->assertEqual(User::find($options, false), $this->bob);
		
		$options['orderBy'] = 'id';
		$this->assertEqual(User::find($options, false), $this->bob);
		
	}
	
	// Test limit.
	public function TestLimit() {
		
		$options = array('limit' => 1);
		$this->assertEqual(User::find($options, false), array($this->bob));
		
		$options['limit'] = '1, 1';
		$this->assertEqual(User::find($options, false), array($this->jim));
		
	}
	
	// Test conditions.
	public function TestConditions() {
		
		$options = array('conditions' => array());
		
		// Start by testing a single condition.
		$options['conditions'] = array('name' => 'Bob');
		$this->assertEqual(User::find($options, false), array($this->bob));
		
		// A multi-condition with a condition operator.
		$options['conditions'] = array('name' => '!= Bob', 'id' => '> 1');
		$this->assertEqual(User::find($options, false), array($this->jim));
		
		// A failing condition.
		$options['conditions'] = array('name' => 'Bob', 'id' => 2);
		$this->assertStrict(User::find($options, false), array());
		
		// Again, but for a 'find first'
		$options['type'] = 'first';
		$this->assertStrict(User::find($options, false), null);
		
		//Finally, a working condition by changing the 'operator'
		$options['operator'] = 'or';
		$options['type'] = 'all';
		$options['orderBy'] = 'id desc';
		$this->assertEqual(User::find($options), array($this->jim, $this->bob));
		
	}
	
}

?>