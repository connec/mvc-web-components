<?php

namespace RegisterTest;

use MVCWebComponents\Register,
	MVCWebComponents\UnitTest\UnitTest;

class RegisterTest extends UnitTest {
	
	public function TestRegister() {
		
		Register::write('array', array('key' => 'value'));
		$this->assertStrict(Register::read('array'), array('key' => 'value'));
		$this->assertStrict(Register::read('array.key'), 'value');
		
		Register::write('array.new_key', 'new_value');
		$this->assertTrue(Register::check('array.new_key'));
		$this->assertFalse(Register::check('array.no_key'));
		
		Register::clear('array.new_key');
		$this->assertFalse(Register::check('array.new_key'));
		
		Register::write('array', array(1,2,3));
		$this->assertStrict(Register::read('array.1'), 2);
		
		Register::clear('array.1');
		$this->assertFalse(Register::check('array.1'));
		
	}
	
}

?>