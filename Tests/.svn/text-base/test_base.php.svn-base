<?php

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
use MVCWebComponents\Database\Database, MVCWebComponents\Model\Model, MVCWebComponents\UnitTest;

set_exception_handler(array('T', 'handleException'));

Database::connect(
	'mysqli_driver',
	array(
		'server' => 'localhost',
		'user' => 'none',
		'database' => 'test_mvc'
	)
);

Class T {
	
	public static function assertEqual($var1, $var2) {
		
		if($var1 == $var2) {
			echo "Passed\n";
		}else {
			ob_start();
			var_dump($var1);
			$var1 = ob_get_clean();
			ob_start();
			var_dump($var2);
			$var2 = ob_get_clean();
			$message = "Test failed:\n$var1\n NOT EQUAL \n$var2\n";
			throw new MVCException($message);
		}
		
	}
	
	public static function assertStrict($var1, $var2) {
		
		if($var1 === $var2) {
			echo "Passed\n";
		}else {
			ob_start();
			var_dump($var1);
			$var1 = ob_get_clean();
			ob_start();
			var_dump($var2);
			$var2 = ob_get_clean();
			$message = "Test failed:\n$var1\n NOT STRICT EQUAL \n$var2\n";
			throw new MVCException($message);
		}
		
	}
	
	public static function handleException($exception) {
		
		echo $exception->getFormattedMsg();
		
		if(is_callable('undo')) undo();
		
	}
	
}

?>