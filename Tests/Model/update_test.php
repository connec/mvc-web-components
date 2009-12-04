<?php

namespace UpdateTest;
use MVCWebComponents\Model\Model, MVCWebComponents\Database\Database, MVCWebComponents\UnitTest\UnitTest;

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
		$this->assertTrue(Post::insert($this->post1));
		
		return true;
		
	}
	
	public function TestUpdate() {
		
		$post1 = clone $this->post1;
		$post1->title = 'New Post 1 Title!';
		$this->assertTrue(Post::update($post1));
		$this->assertFalse(Post::findFirstById($post1->id) == $this->post1);
		$this->assertEqual(Post::findFirstById($post1->id)->title, 'New Post 1 Title!');
		
	}
	
}

?>