<?php

namespace InstanceTest;
use MVCWebComponents\Model\Table, MVCWebComponents\UnitTest\UnitTest;

class InstanceTest extends UnitTest {
	
	public function TestReference() {
		
		$this->assertStrict(Table::instance('posts', 'Post'), Table::instance('posts', 'Post'));
		$this->assertFalse(Table::instance('posts', 'Post') === Table::instance('users', 'User'));
		
	}
	
}

?>