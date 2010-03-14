<?php

namespace RowCountTest;
use MVCWebComponents\UnitTest\UnitTest,
	MVCWebComponents\Model\Model;

class Post extends Model {}

class RowCountTest extends UnitTest {
	
	public $dependencies = array('SaveTest', 'DeleteTest', 'FindTest');
	
	public function TestUpdateOnSave() {
		
		$this->assertStrict(Post::getRowCount(), 0);
		
		$post = new Post(array(
			'category_id' => 1,
			'author_id' => 1,
			'title' => 'Le Title',
			'content' => 'Yo Momma',
			'time' => time()));
		$this->assertTrue($post->save());
		$this->assertStrict(Post::getRowCount(), 1);
		
	}
	
	public function TestUpdateOnDelete() {
		
		$this->assertTrue(Post::findFirst()->delete());
		$this->assertStrict(Post::getRowCount(), 0);
		
	}
	
}

?>