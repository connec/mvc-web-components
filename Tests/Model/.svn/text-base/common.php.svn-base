<?php

use MVCWebComponents\Database\Database;
use MVCWebComponents\Model\Model;

error_reporting(E_ALL);

include_once '../../mvc_exception.php';
include_once '../../set.php';
foreach(scandir('../..') as $file) {
	if($file == '.' or $file == '..') continue;
	
	$file = "../../$file";
	if(is_file($file) and end(explode('.', $file)) == 'php') include_once $file;
}
include_once '../../Database/database.php';
include_once '../../Database/mysqli_driver.php';
include_once '../../Model/model.php';
include_once '../../Model/table.php';

MVCWebComponents\Database\Database::connect(
	'mysqli_driver',
	array(
		'server' => 'localhost',
		'user' => 'none',
		'database' => 'test_mvc'
	)
);

?>