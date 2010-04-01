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
		
		list($jim, $bob) = User::findAll(array(), false);
		$jim->id = 1; // Force the 'touched' flag to change.
		$bob->id = 2;
		$this->assertEqual(array($jim, $bob), array($this->bob, $this->jim));
		
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
		list($bob, $jim) = User::findAll($options, false);
		$bob->id = 1;
		$jim->id = 2;
		$this->assertEqual(array($bob, $jim), array($this->bob, $this->jim));
		
	}
	
	// Test finding a single entry
	public function TestFindFirst() {
		
		$options = array('type' => 'first');
		$bob = User::find($options, false);
		$bob->id = 1;
		$this->assertEqual($bob, $this->bob);
		
	}
	
	// Test order by
	public function TestOrderBy() {
		
		$options = array('orderBy' => 'id desc', 'type' => 'first');
		$jim = User::find($options, false);
		$jim->id = 2;
		$this->assertEqual($jim, $this->jim);
		
		$options['orderBy'] = 'id asc';
		$bob = User::find($options, false);
		$bob->id = 1;
		$this->assertEqual($bob, $this->bob);
		
		$options['orderBy'] = 'id';
		$bob = User::find($options, false);
		$bob->id = 1;
		$this->assertEqual($bob, $this->bob);
		
	}
	
	// Test limit.
	public function TestLimit() {
		
		$options = array('limit' => 1);
		list($bob) = User::find($options, false);
		$bob->id = 1;
		$this->assertEqual(array($bob), array($this->bob));
		
		$options['limit'] = '1, 1';
		list($jim) = User::find($options, false);
		$jim->id = 2;
		$this->assertEqual(array($jim), array($this->jim));
		
	}
	
	// Test conditions.
	public function TestConditions() {
		
		$options = array('conditions' => array());
		
		// Start by testing a single condition.
		$options['conditions'] = array('name' => 'Bob');
		list($bob) = User::find($options, false);
		$bob->id = 1;
		$this->assertEqual(array($bob), array($this->bob));
		
		// A multi-condition with a condition operator.
		$options['conditions'] = array('name' => '!= Bob', 'id' => '> 1');
		list($jim) = User::find($options, false);
		$jim->id = 2;
		$this->assertEqual(array($jim), array($this->jim));
		
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
		list($jim, $bob) = User::find($options);
		$bob->id = 1;
		$jim->id = 2;
		$this->assertEqual(array($jim, $bob), array($this->jim, $this->bob));
		
	}
	
}

?>