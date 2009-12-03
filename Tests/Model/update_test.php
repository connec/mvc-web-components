<?php

namespace UpdateTest;
use MVCWebComponents\Model\Model, MVCWebComponents\Database\Database, MVCWebComponents\UnitTest;

class Post extends Model {}

class UpdateTest extends UnitTest {
	
	public $dependencies = array('InsertTest', 'FindTest', 'CallStaticTest', 'InitTest');
	
	public function preTesting() {
		
		// Put some posts in...
		$this->post1 = (object)array(
			'category_id' => 1,
			'author_id' => 1,
			'title' => 'Post 1 Title',
			'content' => 'Post 1 Content',
			'time' => time());
		$this->post2 = (object)array(
			'category_id' => 1,
			'author_id' => 2,
			'title' => 'Post 2 Title',
			'content' => 'Post 2 Content',
			'time' => time());
		$this->post3 = (object)array(
			'category_id' => 1,
			'author_id' => 1,
			'title' => 'Post 3 Title',
			'content' => 'Post 3 Content',
			'time' => time());
		
		$this->assertTrue(Post::insert($this->post1));
		$this->assertTrue(Post::insert($this->post2));
		$this->assertTrue(Post::insert($this->post3));
		
		return true;
		
	}
	
	public function TestUpdate() {
		
		$post1 = clone $this->post1;
		$post1->title = 'New Post 1 Title!';
		$this->assertTrue(Post::update($post1));
		$this->assertFalse(Post::findFirstById($post1->id) == $this->post1);
		$this->assertEqual(Post::findFirstById($post1->id)->title, 'New Post 1 Title!');
		
	}
	
	public function postTesting() {
		
		return Database::query('truncate table `posts`');
		
	}
	
}

?>