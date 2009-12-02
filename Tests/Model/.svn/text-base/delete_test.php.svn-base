<?php

include_once 'common.php';
use MVCWebComponents\Database\Database, MVCWebComponents\Model\Model, MVCWebComponents\UnitTest;

class Post extends Model {}

class DeleteTest extends UnitTest {
	
	public function preTesting() {
		
		return $this->model = Post::getInstance();
		
	}
	
	public function TestDelete() {
		
		$post = new StdClass;
		$post->category_id = 1;
		$post->author_id = 1;
		$post->title = 'Post Title';
		$post->content = 'Post Content';
		$post->time = time();
		
		$this->assertTrue($this->model->save($post));
		$this->assertTrue($this->model->delete($post));
		$this->assertEqual(count($this->model->findAllById($post->id)), 0);
		
	}
	
	public function postTesting() {
		
		return Database::query('truncate table `posts`');
		
	}
	
}

new DeleteTest;

?>