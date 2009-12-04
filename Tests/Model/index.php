<?php

use MVCWebComponents\Autoloader, MVCWebComponents\Database\Database, MVCWebComponents\UnitTest\TestSuite, MVCWebComponents\UnitTest\UnitTest;

error_reporting(E_ALL | E_STRICT);

require_once '../../autoloader.php';

Autoloader::addDirectory(
	'../..', '../../Model',
	'../../Database',
	'../../UnitTest');

Database::connect(
	'mysqli_driver',
	array(
		'server' => 'localhost',
		'user' => 'none',
		'database' => 'test_mvc'
	)
);

class ModelTests extends TestSuite {
	
	public static function postTest() {
		
		static::assertTrue(Database::query("delete from `users` where `id` <> '1' and `id` <> '2'"));
		static::assertTrue(Database::query('truncate table `posts`'));
		
	}
	
}

ModelTests::runTests();

?>