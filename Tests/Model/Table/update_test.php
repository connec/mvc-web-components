<?php

namespace UpdateTest;
use MVCWebComponents\Model\Model,
	MVCWebComponents\Model\Table,
	MVCWebComponents\Database\Database,
	MVCWebComponents\UnitTest\UnitTest;

class Post extends Model {}

class UpdateTest extends UnitTest {
	
	public $dependencies = array('InsertTest');
	
	public function preTesting() {
		
		$this->table = Table::instance('posts', 'UpdateTest\Post');
		
		// Put some posts in...
		$this->post = new Post(array(
			'category_id' => 1,
			'author_id' => 1,
			'title' => 'Post 1 Title',
			'content' => 'Post 1 Content',
			'time' => time()));
		$this->assertEqual($this->post->id = $this->table->insert($this->post), true);
		
	}
	
	public function TestUpdate() {
		
		$post = clone $this->post;
		$post->title = 'New Post 1 Title!';
		$this->assertTrue($this->table->update($post));
		$this->assertFalse(
			$this->table->find(array('type' => 'first', 'conditions' => array('id' => 1)))
			== array($this->post));
		$this->assertEqual(
			$this->table->find(array('type' => 'first', 'conditions' => array('id' => 1)))->title,
			'New Post 1 Title!');
		
	}
	
}

?>