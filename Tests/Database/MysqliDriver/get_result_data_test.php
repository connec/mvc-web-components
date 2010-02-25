<?php

namespace GetResultDataTest;
use MVCWebComponents\UnitTest\UnitTest,
	MVCWebComponents\Database\MysqliDriver,
	MVCWebComponents\BadArgumentException,
	MVCWebComponents\Database\DatabaseConnectionException;

class GetResultDataTest extends UnitTest {
	
	public $dependencies = array('QueryTest');
	
	public function preTesting() {
		
		$this->mysqli_driver = new MysqliDriver;
		$this->mysqli_driver->connect(array('database' => 'test_mvc', 'user' => 'none'));
		
	}
	
	public function TestNoResultData() {
		
		@$this->assertFalse($this->mysqli_driver->getInsertId());
		@$this->assertFalse($this->mysqli_driver->getNumResultRows());
		@$this->assertEqual($this->mysqli_driver->getNumAffectedRows(),0);
		
	}
	
	public function TestGetInsertId() {
		
		$this->assertTrue(
			$this->mysqli_driver->query("insert into `posts` (`category_id`, `author_id`, `title`, `content`, `time`) values ('1','1','lol','test','" . time() . "');")
		);
		
		$this->assertStrict(
			$this->mysqli_driver->getInsertId(),
			1
		);
		
	}
	
	public function TestNumAffectedRows() {
		
		$this->assertTrue(
			$this->mysqli_driver->query("update `posts` set `title` = 'new'")
		);
		
		$this->assertStrict(
			$this->mysqli_driver->getNumAffectedRows(),
			1
		);
		
	}
	
	public function TestNumResultRows() {
		
		$this->assertTrue(
			$this->mysqli_driver->query('select * from `posts`')
		);
		
		$this->assertStrict(
			$this->mysqli_driver->getNumResultRows(),
			1
		);
		
	}
	
}

?>