<?php

namespace SaveTest;
use MVCWebComponents\Model\Model, MVCWebComponents\Database\Database, MVCWebComponents\UnitTest\UnitTest;

class Post extends Model {}

class SaveTest extends UnitTest {
	
	public $dependencies = array('InsertTest', 'UpdateTest');
	
	public function TestInsertion() {
		
		// Test a new record is inserted.
		$post = new Post(array(
			'category_id' => 1,
			'author_id' => 1,
			'title' => 'Post 1 Title',
			'content' => 'Post 1 content...',
			'time' => time()));
		$this->assertTrue($post->save(array('validate' => false)));
		$this->assertEqual(Post::findFirst(array('cascade' => false)), $post);
		
	}
	
	public function TestUpdating() {
		
		$this->assertEqual($post = Post::findFirst(), true);
		$post->title = 'New Title';
		$this->assertTrue($post->save(array('validate' => false)));
		$this->assertEqual(Post::findFirst()->title, 'New Title');
		
	}
	
}

?>