<?php

namespace QueryTest;
use MVCWebComponents\UnitTest\UnitTest,
	MVCWebComponents\Database\MysqliDriver,
	MVCWebComponents\BadArgumentException,
	MVCWebComponents\Database\DatabaseConnectionException;

class EmptyClass {
	public function __construct($var = null) {
		$this->var = $var;
	}
}

class QueryTest extends UnitTest {
	
	public $dependencies = array('ConnectTest');
	
	public function preTesting() {
		
		$this->mysqli_driver = new MysqliDriver;
		$this->mysqli_driver->connect(array('database' => 'test_mvc', 'user' => 'none'));
		
	}
	
	public function TestBadQuery() {
		
		$this->assertFalse(
			$this->mysqli_driver->query('select * from `bad_table`')
		);
		
		$this->assertEqual(
			$this->mysqli_driver->getError(),
			'Table \'test_mvc.bad_table\' doesn\'t exist'
		);
		
	}
	
	public function TestGoodQuery() {
		
		$this->assertTrue(
			$this->mysqli_driver->query('select `id` from `users`')
		);
		
	}
	
	public function TestGetArray() {
		
		$this->assertEqual(
			$this->mysqli_driver->getArray(),
			array('id' => 1)
		);
		
	}
	
	public function TestRewind() {
		
		$this->mysqli_driver->rewind();
		$this->TestGetArray();
		$this->mysqli_driver->rewind();
		
	}
	
	public function TestGetObject() {
		
		$this->assertEqual(
			$this->mysqli_driver->getObject(),
			(object)array('id' => 1)
		);
		$this->mysqli_driver->rewind();
		
		$class = new EmptyClass;
		$class->id = 1;
		$this->assertEqual(
			$this->mysqli_driver->getObject('\QueryTest\EmptyClass'),
			$class
		);
		
		$class = new EmptyClass('val');
		$class->id = 2;
		$this->assertEqual(
			$this->mysqli_driver->getObject('\QueryTest\EmptyClass', array('val')),
			$class
		);
		
		$this->mysqli_driver->rewind();
		
	}
	
}

?>