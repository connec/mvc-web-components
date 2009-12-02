<?php

include_once 'common.php';
use MVCWebComponents\Model\Model, MVCWebComponents\UnitTest;

class User extends Model {}

class EmptyClass {}

class ConstructClass {
	
	public function __construct($flag) {
		
		$this->flag = $flag;
		
	}
	
}

class FindTest extends UnitTest {
	
	// Assign a common model.
	public function preTesting() {
		
		return $this->model = User::getInstance();
		
	}
	
	// Make sure all the options have the desired effect.
	public function TestAllOptions() {
		
		$conditions = array(
			'conditions' => array('joined' => 1251141357),
			'fields' => array('id', 'joined'),
			'type' => 'all',
			'return' => array('ConstructClass', array(true)),
			'orderBy' => 'id desc',
			'limit' => 0,
			'operator' => 'and',
			'cascade' => true,
			'processed' => array(),
			'sql' => ''
		);
		
		$users = $this->model->find($conditions);
		
		$this->assertTrue(is_array($users));
		$this->assertEqual(count($users), 2);
		$this->assertTrue($users[0]->id > $users[count($users) - 1]->id);
		foreach($users as $user) {
			$this->assertStrict($user->joined, 1251141357);
			$this->assertTrue(isset($user->id) and isset($user->joined) and isset($user->flag));
			$this->assertTrue($user->flag);
			$this->assertTrue($user instanceof ConstructClass);
		}
		
	}
	
	// Test 'first' find type.
	public function TestFindTypeFirst() {
		
		$user = $this->model->find(array('type' => 'first'));
		
		$this->assertTrue($user instanceof \StdClass);
		$this->assertStrict($user->id, 1);
		
	}
	
	// Test conditions operators.
	public function TestConditionOperators() {
		
		$options = array('conditions' => array('joined' => '<> 1251141357'));
		$this->assertStrict($this->model->find($options), array());
		
		$options = array('conditions' => array('joined' => '!= 1251141357'));
		$this->assertStrict($this->model->find($options), array());
		
		$options = array('conditions' => array('joined' => '> 1', 'name' => '~ bob'), 'type' => 'first', 'return' => 'EmptyClass');
		$user = $this->model->find($options);
		$this->assertEqual($user->name, 'Bob');
		$this->assertTrue($user instanceof EmptyClass);
		
		$options = array('conditions' => array('name' => 'in Bob,Jim'));
		$this->assertEqual(count($this->model->find($options)), 2);
		
	}
	
}

new FindTest;

?>