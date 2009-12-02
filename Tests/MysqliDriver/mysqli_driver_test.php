<?php

include '../../mvc_exception.php';
include '../../singleton.php';
include '../../database.php';
include '../../mysqli_driver.php';

echo '<pre>';

$driver = new MysqliDriver;
$driver->connect(null, 'none', null, 'tutorial3');
$driver->query('select * from `flight`');
print_r($driver->getRow());
print_r($driver->getRow());
$driver->rewind();
print_r($driver->getRow());
var_dump($driver->getError());
var_dump($driver->getInsertId());
var_dump($driver->getNumResultRows());
var_dump($driver->getNumAffectedRows());

try {
	$driver->query('LOLZ');
}catch(Exception $exception) {}
print_r($driver->getRow());

print_r($driver);

echo '</pre>';

?>