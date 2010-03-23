<?php

namespace SetTest;

use MVCWebComponents\Set,
	MVCWebComponents\UnitTest\UnitTest;

class Reg extends Set {}

class Reg2 extends Set {}

class SetTest extends UnitTest {
	
	public function TestRead() {
		
		Reg::write('array', array('key' => 'value'));
		$this->assertStrict(Reg::read('array'), array('key' => 'value'));
		$this->assertStrict(Reg::read('array.key'), 'value');
		
	}
	
	public function TestWrite() {
		
		Reg::write('array.new_key', 'new_value');
		$this->assertStrict(Reg::read('array.new_key'), 'new_value');
		
	}
	
	public function TestCheck() {
		
		$this->assertTrue(Reg::check('array.new_key'));
		$this->assertFalse(Reg::check('array.no_key'));
		
	}
	
	public function TestClear() {
		
		Reg::clear('array.new_key');
		$this->assertFalse(Reg::check('array.new_key'));
		
	}
	
	public function TestIntKeys() {
		
		Reg::write('array', array(1,2,3));
		$this->assertStrict(Reg::read('array.1'), 2);
		
		Reg::clear('array.1');
		$this->assertFalse(Reg::check('array.1'));
		
	}
	
	public function TestAppend() {
		
		Reg::append('array', 4);
		$this->assertStrict(Reg::read('array.3'), 4);
		
		Reg::append('array.3', 3);
		$this->assertStrict(Reg::read('array.3'), 7);
		
		Reg::write('array.key', 'string');
		Reg::append('array.key', 'more');
		$this->assertStrict(Reg::read('array.key'), 'stringmore');
		
	}
	
	public function TestIndependence() {
		
		Reg::write('a', 1);
		$this->assertFalse(Reg2::check('a'));
		
	}
	
}

?>