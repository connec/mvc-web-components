<?php

namespace GetInstanceTest;
use MVCWebComponents\Model\Table, MVCWebComponents\UnitTest\UnitTest;

class GetInstanceTest extends UnitTest {
	
	public function TestReference() {
		
		$this->assertStrict(Table::getInstance('posts'), Table::getInstance('posts'));
		$this->assertFalse(Table::getInstance('posts') === Table::getInstance('users'));
		
	}
	
}

?>