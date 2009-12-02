<?php

include 'common.php';

class Post extends Model {}

echo '<pre>';

$model = Post::getInstance();

$post1 = new StdClass;
$post1->category_id = 1;
$post1->author_id = 1;
$post1->title = 'Post Title';
$post1->content = 'Post Content';
$post1->time = time();

$post2 = new StdClass;
$post2->id = 999;
$post2->category_id = 1;
$post2->author_id = 1;
$post2->title = 'Post2 Title';
$post2->content = 'Post2 Content';
$post2->time = time();

T::assertStrict($model->save($post1), true);
$post1->id = Database::getInsertId();
T::assertStrict($model->save($post2), true);

T::assertEqual($post1, $model->find(array('conditions' => array('title' => 'Post Title'), 'type' => 'first')));
T::assertEqual($post2, $model->find(array('conditions' => array('id' => 999), 'type' => 'first')));

$post2->title = 'Updated Post2 Title';

T::assertStrict($model->save($post2), true);

T::assertEqual($post2, $model->find(array('conditions' => array('id' => 999), 'type' => 'first')));

Database::query("delete from `posts` where `id` = 999 or `title` = 'Post Title'");

echo '</pre>';

?>