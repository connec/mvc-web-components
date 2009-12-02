<?php

include '../../mvc_exception.php';
include '../../inflector.php';
include '../../singleton.php';
include '../../database.php';
include '../../mysqli_driver.php';

echo '<pre>';

var_dump(Database::connect('MysqliDriver', 'localhost', 'none', '', 'tutorial3'));
var_dump(Database::query('select * from `flight`'));
print_r(Database::getRow());
print_r(Database::getAll());

print_r(Database::getQueries());

echo '</pre>';

?>