<?php

use MVCWebComponents\Autoloader,
	MVCWebComponents\Database\Database,
	MVCWebComponents\UnitTest\TestSuite,
	MVCWebComponents\Model\Table;

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

class ModelTests extends TestSuite {
	
	public static $ensurePostTesting = true;
	
	public static function preTest() {
		
		Database::query('select * from `posts`');
		static::assertStrict(Database::getNumResultRows(), 0);
		
		Database::query('select * from `users`');
		static::assertStrict(Database::getNumResultRows(), 2);
		
		Table::instance('users')->updateRowCount();
		Table::instance('posts')->updateRowCount();
		
	}
	
	public static function postTest() {
		
		static::assertTrue(Database::query("delete from `users` where `id` <> '1' and `id` <> '2'"));
		static::assertTrue(Database::query('truncate table `posts`'));
		
	}
	
}

ModelTests::runTests();

?>