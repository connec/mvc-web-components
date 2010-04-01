<?php

namespace FindTest;
use MVCWebComponents\Model\Model,
	MVCWebComponents\Model\Table,
	MVCWebComponents\UnitTest\UnitTest;

class User extends Model {}

class FindTest extends UnitTest {
	
	// This test assumes InitTest passes.
	public $dependencies = array('ConstructTest', 'InstanceTest');
	
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
		
		$this->table = Table::instance('users', 'FindTest\User');
		
	}
	
	public function TestDefaults() {
		
		list($bob, $jim) = $this->table->find(array(), false);
		$bob->id = 1;
		$jim->id = 2;
		$this->assertEqual(array($bob, $jim), array($this->bob, $this->jim));
		
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
		list($bob, $jim) = $this->table->find($options, false);
		$bob->id = 1;
		$jim->id = 2;
		$this->assertEqual(array($bob, $jim), array($this->bob, $this->jim));
		
	}
	
	// Test finding a single entry
	public function TestFindFirst() {
		
		$options = array('type' => 'first');
		$bob = $this->table->find($options, false);
		$bob->id = 1;
		$this->assertEqual($bob, $this->bob);
		
	}
	
	// Test order by
	public function TestOrderBy() {
		
		$options = array('orderBy' => 'id desc', 'type' => 'first');
		$jim = $this->table->find($options, false);
		$jim->id = 2;
		$this->assertEqual($jim, $this->jim);
		
		$options['orderBy'] = 'id asc';
		$bob = $this->table->find($options, false);
		$bob->id = 1;
		$this->assertEqual($bob, $this->bob);
		
		$options['orderBy'] = 'id';
		$bob = $this->table->find($options, false);
		$bob->id = 1;
		$this->assertEqual($bob, $this->bob);
		
	}
	
	// Test limit.
	public function TestLimit() {
		
		$options = array('limit' => 1);
		list($bob) = $this->table->find($options, false);
		$bob->id = 1;
		$this->assertEqual(array($bob), array($this->bob));
		
		$options['limit'] = '1, 1';
		list($jim) = $this->table->find($options, false);
		$jim->id = 2;
		$this->assertEqual(array($jim), array($this->jim));
		
	}
	
	// Test conditions.
	public function TestConditions() {
		
		$options = array('conditions' => array());
		
		// Start by testing a single condition.
		$options['conditions'] = array('name' => 'Bob');
		list($bob) = $this->table->find($options, false);
		$bob->id = 1;
		$this->assertEqual(array($bob), array($this->bob));
		
		// A multi-condition with a condition operator.
		$options['conditions'] = array('name' => '!= Bob', 'id' => '> 1');
		list($jim) = $this->table->find($options, false);
		$jim->id = 2;
		$this->assertEqual(array($jim), array($this->jim));
		
		// A failing condition.
		$options['conditions'] = array('name' => 'Bob', 'id' => 2);
		$this->assertStrict($this->table->find($options, false), array());
		
		// Again, but for a 'find first'
		$options['type'] = 'first';
		$this->assertStrict($this->table->find($options, false), null);
		
		//Finally, a working condition by changing the 'operator'
		$options['operator'] = 'or';
		$options['type'] = 'all';
		$options['orderBy'] = 'id desc';
		list($jim, $bob) = $this->table->find($options);
		$bob->id = 1;
		$jim->id = 2;
		$this->assertEqual(array($jim, $bob), array($this->jim, $this->bob));
		
	}
	
}

?>