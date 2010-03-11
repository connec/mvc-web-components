<?php

namespace SessionTest;

use MVCWebComponents\Session,
	MVCWebComponents\UnitTest\UnitTest;

session_start();

class SessionTest extends UnitTest {
	
	public function TestSession() {
		
		Session::write('array', array('key' => 'value'));
		$this->assertStrict(Session::read('array'), array('key' => 'value'));
		$this->assertStrict(Session::read('array.key'), 'value');
		$this->assertStrict(Session::read('array'), $_SESSION['array']);
		$this->assertStrict(Session::read('array.key'), $_SESSION['array']['key']);
		
		Session::write('array.new_key', 'new_value');
		$this->assertTrue(Session::check('array.new_key'));
		$this->assertFalse(Session::check('array.no_key'));
		$this->assertTrue(isset($_SESSION['array']['new_key']));
		$this->assertFalse(isset($_SESSION['array']['no_key']));
		
		Session::clear('array.new_key');
		$this->assertFalse(Session::check('array.new_key'));
		$this->assertFalse(isset($_SESSION['array']['new_key']));
		
		Session::write('array', array(1,2,3));
		$this->assertStrict(Session::read('array.1'), 2);
		$this->assertStrict($_SESSION['array'][1], 2);
		
		Session::clear('array.1');
		$this->assertFalse(Session::check('array.1'));
		$this->assertFalse(isset($_SESSION['array'][1]));
		
	}
	
}

?>