<?php

namespace ConnectTest;
use MVCWebComponents\UnitTest\UnitTest,
	MVCWebComponents\Database\MysqliDriver,
	MVCWebComponents\BadArgumentException,
	MVCWebComponents\Database\DatabaseConnectionException;

class ConnectTest extends UnitTest {
	
	public function preTesting() {
		
		$this->mysqli_driver = new MysqliDriver;
		
	}
	
	public function TestConnectFailing() {
		
		try {
			@$this->mysqli_driver->connect();
		} catch(BadArgumentException $e) {
			$a = true;
		}
		$this->assertTrue($a);
		
		try {
			@$this->mysqli_driver->connect(array('database' => 'Doesn\'t Exist', 'user' => 'invalid'));
		} catch(DatabaseConnectionException $e) {
			$b = true;
		}
		$this->assertTrue($b);
		
	}
	
	public function TestConnect() {
		
		$this->assertTrue(
			$this->mysqli_driver->connect(array('database' => 'test_mvc', 'user' => 'none'))
		);
		
	}
	
}

?>