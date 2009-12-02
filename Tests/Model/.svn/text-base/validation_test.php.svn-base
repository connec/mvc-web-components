<?php

include '../test_base.php';
use MVCWebComponents\Model\Model;

function is_multiple_of_five($test) {
	return ($test % 5 == 0);
}

Class User extends Model {
	
	protected $validate = array(
		'id' => array('unique', 'numeric'),
		'name' => array('maxlength' => 32, 'unique', 'required'),
		'password' => array('maxlength' => 64, 'minlength' => 6, 'required'),
		'user_group_id' => array('numeric'),
		'date' => array('dateformat' => 'Y-m-d H:i:s'),
		'five' => array('callback' => 'is_multiple_of_five')
	);
	
}

echo '<pre>';

$model = User::getInstance();

$user = new StdClass;
$user->name = 'Jimminy Billybob';
$user->password = '1';
T::assertEqual($model->validate($user), array('password' => array('minlength' => 6)));
T::assertStrict($model->validate($user, array('all' => array('minlength'))), true);

$user = new StdClass;
$user->name = 'Bob';
T::assertEqual($model->validate($user), array('name' => array('unique' => 'unique'), 'password' => array('required' => 'required')));

$user = new StdClass;
$user->name = 'Hello World';
$user->password = 'Password';
$user->date = date('Y-m-d H:i:s');
T::assertStrict($model->validate($user), true);

$user = new StdClass;
$user->name = 'Bobfosheezy';
$user->password = 'Password';
$user->five = 10;
T::assertStrict($model->validate($user), true);

echo '</pre>';

?>