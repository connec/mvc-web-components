<?php

namespace ConstructTest;
use MVCWebComponents\Model\Table, MVCWebComponents\UnitTest\UnitTest;

class ConstructTest extends UnitTest {
	
	public $dependencies = array('GetInstanceTest');
	
	public function TestValues() {
		
		$table = Table::getInstance('users');
		
		$this->assertStrict($table->getName(), 'users');
		$this->assertStrict($table->getFields(), array('id','name','password','user_group_id','joined'));
		$this->assertStrict($table->getPrimaryKey(), 'id');
		$this->assertStrict($table->getDefaultRecord(), array(
			'id' => null,
			'name' => null,
			'password' => null,
			'user_group_id' => null,
			'joined' => null));
		
	}
	
}

?>