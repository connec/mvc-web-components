<?php

use MVCWebComponents\Autoloader, MVCWebComponents\Database\MysqliDriver, MVCWebComponents\UnitTest\TestSuite;

error_reporting(E_ALL | E_STRICT);

require_once '../../../autoloader.php';
require_once '../../../mvc_exception.php';
require_once '../../../extensible_static.php';
require_once '../../../hookable.php';
require_once '../../../Database/database.php'; // Include Database first to load database interfaces and exceptions.

Autoloader::addDirectory(
	'../../..',
	'../../../Model',
	'../../../Database',
	'../../../UnitTest');

class MysqliDriverTests extends TestSuite {
	
	public static $conn;
	
	public static $ensurePostTesting = true;
	
	public static function preTest() {
		
		mysql_connect('localhost', 'none');
		mysql_select_db('test_mvc');
		$result = mysql_query('select * from `posts`');
		static::assertStrict(mysql_num_rows($result), 0);
		
		$result = mysql_query('select * from `users`');
		static::assertStrict(mysql_num_rows($result), 2);
		
	}
	
	public static function postTest() {
		
		static::assertTrue(mysql_query("delete from `users` where `id` <> '1' and `id` <> '2'"));
		static::assertTrue(mysql_query('truncate table `posts`'));
		
	}
	
}

MysqliDriverTests::runTests();

?>