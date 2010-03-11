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
		
		$this->assertStrict(Autoloader::$directories, array(realpath('.') . DIRECTORY_SEPARATOR));
		
		try {
			Autoloader::addDirectory('missing');
		}catch(MissingDirectoryException $e) {
			$a = true;
		}
		$this->assertTrue($a);
		
		Autoloader::addDirectory('../..');
		
		$this->assertFalse(\MVCWebComponents\Register::check('missing'));
		
		try {
			new NoClass();
		}catch(MissingClassException $e) {
			$b = true;
		}
		$this->assertTrue($b);
		
	}
	
}

?>