<?php

include_once 'common.php';
use MVCWebComponents\Model\Model, MVCWebComponents\UnitTest;

class User extends Model {}

class CallTest extends UnitTest {
	
	public function preTesting() {
		
		return $this->model = User::getInstance();
		
	}
	
	// Test the findFirst 'magic' function.
	public function TestFindFirst() {
		
		$firstUser = $this->model->findFirst();
		$lastUser = $this->model->findFirst(array('orderBy' => 'id desc'));
		
		$this->assertTrue($lastUser->id > $firstUser->id);
		
	}
	
	// Test the findFirstByFieldName 'magic' function.
	public function TestFindFirstBy() {
		
		$user1 = $this->model->findFirstById(1);
		$user2 = $this->model->findFirstByJoined(1251141357);
		
		$this->assertStrict($user1->id, 1);
		$this->assertStrict($user2->joined, 1251141357);
		
	}
	
	// Test the findAll 'magic' function.
	public function TestFindAll() {
		
		$users = $this->model->findAll(array('orderBy' => 'id desc'));
		
		$this->assertTrue(is_array($users));
		$this->assertTrue($users[0]->id > $users[count($users) - 1]->id);
		
	}
	
	// Test the findAllBy 'magic' function.
	public function TestFindAllBy() {
		
		$users1 = $this->model->findAllByName('lol');
		$users2 = $this->model->findAllByJoined(1251141357);
		
		$this->assertStrict($users1, array());
		$this->assertStrict(count($users2), 2);
		
	}
	
}

new CallTest;

?>