<?php

namespace ConstructTest;
use MVCWebComponents\Model\Table, MVCWebComponents\UnitTest\UnitTest;

class ConstructTest extends UnitTest {
	
	public $dependencies = array('InstanceTest');
	
	public function TestValues() {
		
		$table = Table::instance('users');
		
		$this->assertStrict($table->getName(), 'users');
		$this->assertStrict($table->getFields(), array('id','name','password','user_group_id','joined'));
		$this->assertStrict($table->getPrimaryKey(), 'id');
		$this->assertStrict($table->getDefaultRecord(), array(
			'name' => null,
			'password' => null,
			'user_group_id' => null,
			'joined' => null));
		$this->assertStrict($table->getRowCount(), 2);
		
	}
	
}

?>