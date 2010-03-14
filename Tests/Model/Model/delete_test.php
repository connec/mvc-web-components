<?php

namespace DeleteTest;
use MVCWebComponents\Model\Model, MVCWebComponents\Database\Database, MVCWebComponents\UnitTest\UnitTest;

class Post extends Model {}

class DeleteTest extends UnitTest {
	
	public $dependencies = array('CallStaticTest', 'FindTest', 'SaveTest');
	
	public function TestDelete() {
		
		$post = new Post(array(
			'category_id' => 1,
			'author_id' => 1,
			'title' => 'Post Title',
			'content' => 'Post Content',
			'time' => time()));
		$this->assertTrue($post->save(array('validate' => false)));
		$this->assertEqual(Post::findFirst(), $post);
		$this->assertTrue($post->delete());
		$this->assertEqual(Post::findAll(), array());
		
	}
	
}

?>