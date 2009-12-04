<?php

namespace DeleteTest;
use MVCWebComponents\Model\Model, MVCWebComponents\Database\Database, MVCWebComponents\UnitTest\UnitTest;

class Post extends Model {}

class DeleteTest extends UnitTest {
	
	public $dependencies = array('CallStaticTest', 'FindTest', 'SaveTest');
	
	public function TestDelete() {
		
		$post = (object)array(
			'category_id' => 1,
			'author_id' => 1,
			'title' => 'Post Title',
			'content' => 'Post Content',
			'time' => time());
		$this->assertTrue(Post::save($post));
		$this->assertEqual(Post::findFirst(), $post);
		$this->assertTrue(Post::delete($post));
		$this->assertEqual(Post::findAll(), array());
		
	}
	
}

?>