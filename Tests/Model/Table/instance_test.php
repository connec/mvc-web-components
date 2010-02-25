<?php

namespace InstanceTest;
use MVCWebComponents\Model\Table, MVCWebComponents\UnitTest\UnitTest;

class InstanceTest extends UnitTest {
	
	public function TestReference() {
		
		$this->assertStrict(Table::instance('posts'), Table::instance('posts'));
		$this->assertFalse(Table::instance('posts') === Table::instance('users'));
		
	}
	
}

?>