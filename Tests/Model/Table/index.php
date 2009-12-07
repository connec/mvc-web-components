<?php

use MVCWebComponents\Autoloader, MVCWebComponents\Database\Database, MVCWebComponents\UnitTest\TestSuite;

error_reporting(E_ALL | E_STRICT);

require_once '../../../autoloader.php';

Autoloader::addDirectory(
	'../../..',
	'../../../Model',
	'../../../Database',
	'../../../UnitTest');

Database::connect(
	'mysqli_driver',
	array(
		'server' => 'localhost',
		'user' => 'none',
		'database' => 'test_mvc'
	)
);

TestSuite::runTests();

?>