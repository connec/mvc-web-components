<?php

namespace SetTest;

use MVCWebComponents\Set,
	MVCWebComponents\UnitTest\UnitTest;

class Reg extends Set {}

class Reg2 extends Set {}

class SetTest extends UnitTest {
	
	public function TestSet() {
		
		Reg::write('array', array('key' => 'value'));
		$this->assertStrict(Reg::read('array'), array('key' => 'value'));
		$this->assertStrict(Reg::read('array.key'), 'value');
		
		Reg::write('array.new_key', 'new_value');
		$this->assertTrue(Reg::check('array.new_key'));
		$this->assertFalse(Reg::check('array.no_key'));
		
		Reg::clear('array.new_key');
		$this->assertFalse(Reg::check('array.new_key'));
		
		Reg::write('array', array(1,2,3));
		$this->assertStrict(Reg::read('array.1'), 2);
		
		Reg::clear('array.1');
		$this->assertFalse(Reg::check('array.1'));
		
	}
	
	public function TestIndependence() {
		
		Reg::write('a', 1);
		$this->assertFalse(Reg2::check('a'));
		
	}
	
}

?>