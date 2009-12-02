<?php

include 'common.php';
use MVCWebComponents\Model\Model, MVCWebComponents\UnitTest;

class User extends Model {}

class TableTest extends UnitTest {
	
	public function preTesting() {
		
		return $this->model = User::getInstance();
		
	}
	
	# Test the table object has the right data.
	public function TestData() {
		
		$this->assertStrict($this->model->getTableName(), 'users');
		$this->assertStrict($this->model->getPrimaryKey(), 'id');
		$this->assertStrict($this->model->getFields(), array('id','name','password','user_group_id','joined'));
		
	}
	
}

new TableTest;

?>