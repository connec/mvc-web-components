<?php

namespace UpdateTest;
use MVCWebComponents\Model\Model, MVCWebComponents\Database\Database, MVCWebComponents\UnitTest\UnitTest;

class Post extends Model {}

class UpdateTest extends UnitTest {
	
	public $dependencies = array('InsertTest', 'FindTest', 'CallStaticTest', 'InitTest');
	
	public function preTesting() {
		
		// Put some posts in...
		$this->post = new Post(array(
			'category_id' => 1,
			'author_id' => 1,
			'title' => 'Post 1 Title',
			'content' => 'Post 1 Content',
			'time' => time()));
		$this->assertTrue($this->post->insert());
		
	}
	
	public function TestUpdate() {
		
		$post = clone $this->post;
		$post->title = 'New Post 1 Title!';
		$this->assertTrue($post->update());
		$this->assertFalse(Post::findFirstById($post->id) == $this->post);
		$this->assertEqual(Post::findFirstById($post->id)->title, 'New Post 1 Title!');
		
	}
	
}

?>