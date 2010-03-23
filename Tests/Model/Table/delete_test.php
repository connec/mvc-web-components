<?php

namespace DeleteTest;
use MVCWebComponents\Model\Model,
	MVCWebComponents\Model\Table,
	MVCWebComponents\Database\Database,
	MVCWebComponents\UnitTest\UnitTest;

class Post extends Model {}

class DeleteTest extends UnitTest {
	
	public $dependencies = array('FindTest', 'InsertTest');
	
	public function TestDelete() {
		
		$this->table = Table::instance('posts', 'DeleteTest\Post');
		
		$post = new Post(array(
			'category_id' => 1,
			'author_id' => 1,
			'title' => 'Post Title',
			'content' => 'Post Content',
			'time' => time()));
		$this->assertEqual($this->table->insert($post), true);
		$post->id = 1;
		$this->assertEqual($this->table->find(array('type' => 'first', 'conditions' => array('id' => 1))), $post);
		
		$this->assertTrue($this->table->delete($post));
		$this->assertEqual($this->table->find(), array());
		
	}
	
}

?>