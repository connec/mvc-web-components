<?php

include '../test_base.php';
use MVCWebComponents\Model\Model;

Class User extends Model {}

echo '<pre>';

$model = User::getInstance();

$original = $model->find(array('conditions' => array('id' => 1), 'type' => 'first'));
$new = clone $original;
$new->password = 'bobsnewpass';

T::assertStrict($model->update($new), true);

T::assertEqual($new, $model->find(array('conditions' => array('id' => 1), 'type' => 'first')));

T::assertStrict($model->update($original), true);

T::assertEqual($original, $model->find(array('conditions' => array('id' => 1), 'type' => 'first')));

echo '</pre>';

?>