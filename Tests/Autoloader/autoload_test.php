<?php

namespace AutoloadTest;

use MVCWebComponents\Autoloader,
	MVCWebComponents\UnitTest\UnitTest,
	MVCWebComponents\MissingDirectoryException,
	MVCWebComponents\MissingClassException;

require_once '../../autoloader.php';
require_once '../../UnitTest/unit_test.php';

class AutoloadTest extends UnitTest {
	
	public function TestAutoloader() {
		
		$this->assertStrict(spl_autoload_functions(), array(array('MVCWebComponents\Autoloader', 'autoload')));
		
		$this->assertStrict(Autoloader::$directories, array());
		
		$a = false;
		try {
			Autoloader::addDirectory('missing');
		}catch(MissingDirectoryException $e) {
			$a = true;
		}
		$this->assertTrue($a);
		
		$a = false;
		try {
			Autoloader::addDirectory('missing', false);
		}catch(MissingDirectoryException $e) {
			$b = true;
		}
		$this->assertFalse($a);
		
		Autoloader::addDirectory('../..');
		
		$this->assertFalse(\MVCWebComponents\Register::check('missing'));
		
	}
	
}

?>