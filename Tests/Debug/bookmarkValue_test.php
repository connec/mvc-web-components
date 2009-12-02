<?php

include '../test_base.php';

echo '<pre>';

$var = 4;
Debug::watch('var', $var);
Debug::bookmark('var', 'init');
function multiply($var, $n){return $var * $n;}
$var = multiply($var, 5);
Debug::bookmark('var', 'post_multiply');
class TestBookmark {
	public $var;
	public function __construct($value) {
		$this->var = (string)$value;
		Debug::watch('var', $this->var);
		Debug::bookmark('var', 'post_construct');
		$this->doSomething();
	}
	public function doSomething() {
		$this->var .= ' value';
		Debug::bookmark('var', 'post_something');
	}
}
$test = new TestBookmark($var);
Debug::bookmark('var', 'post_class');

T::assertStrict(Debug::bookmarkValue('var', 'init'), 4);
T::assertStrict(Debug::bookmarkValue('var', 'post_multiply'), 20);
T::assertStrict(Debug::bookmarkValue('var', 'post_construct'), '20');
T::assertStrict(Debug::bookmarkValue('var', 'post_something'), '20 value');
T::assertStrict(Debug::bookmarkValue('var', 'post_class'), '20 value');
T::assertStrict(Debug::bookmarkValue('var', 'post_class'), Debug::watchValue('var'));

Debug::bookmarkTable('var');

?>