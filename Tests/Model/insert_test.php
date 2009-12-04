<?php

namespace InsertTest;
use MVCWebComponents\Model\Model, MVCWebComponents\Database\Database, MVCWebComponents\UnitTest\UnitTest;

class Post extends Model {}

class InsertTest extends UnitTest {
	
	public $dependencies = array('CallStaticTest', 'FindTest', 'InitTest');
	
	public function TestBasicInsert() {
		
		$post = (object)array(
			'category_id' => 1,
			'author_id' => 1,
			'title' => 'Le Title',
			'content' => 'Yo Momma',
			'time' => time());
		$this->assertTrue(Post::insert($post));
		$this->assertEqual(Post::findFirstById($post->id), $post);
		
		// Failing test: duplicate primary key
		// $this->assertFalse(Post::insert($post));
		
	}
	
	public function TestWithCrazyCharacters() {
		
		$post = (object)array(
			'category_id' => 1,
			'author_id' => 1,
			'title' => 'Another Title',
			'content' => "\n\0<>!?$%';SELECT * FROM `secrit`;&^\"'\'<|24ZY <><4|24<T3|2Z",
			'time' => time());
		$this->assertTrue(Post::insert($post));
		$this->assertEqual(Post::findFirstById($post->id), $post);
		
	}
	
}

?>