<?php

use MVCWebComponents\Autoloader,
	MVCWebComponents\UnitTest\TestSuite;

error_reporting(E_ALL | E_STRICT);

require_once '../../autoloader.php';

Autoloader::addDirectory(
	'../..',
	'../../Model',
	'../../Database',
	'../../UnitTest');

TestSuite::runTests();

?>