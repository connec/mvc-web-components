<?php

include_once 'common.php';
use MVCWebComponents\Database\Database, MVCWebComponents\Model\Model, MVCWebComponents\UnitTest;

class Post extends Model {}

class InsertTest extends UnitTest {
	
	public function preTesting() {
		
		return $this->model = Post::getInstance();
		
	}
	
	public function TestInsert() {
		
		$post = new StdClass;
		$post->category_id = 1;
		$post->author_id = 1;
		$post->title = 'New Post';
		$post->content = 'Post Content.';
		$post->time = time();
		
		$this->assertTrue($this->model->insert($post));
		$this->assertEqual($this->model->findFirstByTitle('New Post'), $post);
		
	}
	
	public function TestWithCrazyCharacters() {
		
		$post = new StdClass;
		$post->category_id = 1;
		$post->author_id = 1;
		$post->title = 'Another New Post';
		$post->content = "More Post Content \'\"%\0";
		$post->time = time();
		
		$this->assertTrue($this->model->insert($post));
		
		$this->assertEqual($_post = $this->model->findFirstByTitle('Another New Post'), $post);
		
	}
	
	/*public function TestFailing() {
		
		$post = new StdClass;
		$post->category_id = 1;
		$post->author_id = 1;
		$post->title = 'New Post'; // Duplicate title...
		$post->content = "Doesn't matter...";
		$post->time = time();
		
		$this->assertFalse($this->model->insert($post));
		
	}*/
	
	public function postTesting() {
		
		return Database::query('truncate table `posts`');
		
	}
	
}

new InsertTest;

?>