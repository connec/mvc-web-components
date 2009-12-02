<?php

include '../test_base.php';

echo '<pre>';

$var1 = 'Hello';
Debug::watch('var1', $var1);
unset($var1);
T::assertEqual('Hello', Debug::watchValue('var1'));

$var2 = 5;
Debug::watch('var2', $var2);
$var2 = 3;
T::assertEqual(3, Debug::watchValue('var2'));

$var3 = 10;
function addOne(&$var3) {$var3 += 1;}
Debug::watch('var3', $var3);
addOne($var3);
T::assertEqual(11, Debug::watchValue('var3'));

class TestWatch {
	public $var4 = 5;
	public function __construct($n) {
		Debug::watch('var4', $this->var4);
		$this->var4 += $n;
	}
}
$tw = new TestWatch(5);
T::assertEqual(10, Debug::watchValue('var4'));
$tw->var4 += 5;
T::assertEqual(15, Debug::watchValue('var4'));

Debug::watchTable();

echo '</pre>';

?>