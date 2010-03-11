<?php

use MVCWebComponents\UnitTest\TestSuite;

error_reporting(E_ALL | E_STRICT);

require_once '../../mvc_exception.php';
require_once '../../UnitTest/test_suite.php';

TestSuite::runTests();

?>