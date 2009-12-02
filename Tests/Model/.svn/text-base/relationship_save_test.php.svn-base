<?php

error_reporting(E_ALL);

include '../test_base.php';
use MVCWebComponents\Database\Database, MVCWebComponents\Model\Model, MVCWebComponents\UnitTest;

function undo() {
	
	Database::$debugging = false;
	Database::query("delete from `users` where name = 'Minty'");
	Database::query('truncate table `posts`');
	
}

class User extends Model {
	
	protected $hasMany = array(
		'Post' => array(
			'foreignKey' => 'author_id'
		)
	);
	
}

class Post extends Model {
	
	protected $belongsTo = array(
		'Author' => array(
			'model' => 'User',
		)
	);
	
}

echo '<pre>';

$userModel = User::getInstance();
$postModel = Post::getInstance();

$user = new StdClass;
$user->name = 'Minty';
$user->password = 'mintypass';
$user->user_group_id = 1;
$user->joined = time();

$post1 = new StdClass;
$post1->category_id = 1;
$post1->title = 'Post 1 Title';
$post1->content = 'Post 1 Content';
$post1->time = time();

$post2 = new StdClass;
$post2->category_id = 1;
$post2->title = 'Post 2 Title';
$post2->content = 'Post 2 Content';
$post2->time = time();

$post = clone $post1;
$post->Author = clone $user;

$user->Posts = array($post1, $post2);

//Database::$debugging = true;

T::assertStrict(true, $userModel->save($user, array('cascade' => true, 'validate' => true)));
$foundUser = $userModel->findFirst(array('orderBy' => 'id desc'));
T::assertEqual(count($foundUser->Posts), 2);
undo();

T::assertStrict(true, $postModel->save($post, array('cascade' => true, 'validate' => true)));
$foundPost = $postModel->findFirst();
T::assertEqual($foundPost->author_id, $post->Author->id);
T::assertEqual($foundPost->Author->name, 'Minty');
undo();

echo '</pre>';

?>