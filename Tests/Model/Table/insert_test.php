<?php

namespace InsertTest;
use MVCWebComponents\Model\Model,
	MVCWebComponents\Model\Table,
	MVCWebComponents\Database\Database,
	MVCWebComponents\UnitTest\UnitTest;

class Post extends Model {}

class InsertTest extends UnitTest {
	
	public $dependencies = array('ConstructTest', 'InstanceTest', 'FindTest');
	
	public function TestBasicInsert() {
		
		$this->table = Table::instance('posts', 'InsertTest\Post');
		
		$post = new Post(array(
			'category_id' => 1,
			'author_id' => 1,
			'title' => 'Le Title',
			'content' => 'Yo Momma',
			'time' => time()));
		$this->assertStrict($this->table->insert($post), 1);
		$this->assertEqual($this->table->find(array('type' => 'first', 'conditions' => array('id' => 1))), $post);
		
		// Failing test: duplicate primary key
		// $this->assertFalse(Post::insert($post));
		
	}
	
	public function TestWithCrazyCharacters() {
		
		$post = new Post(array(
			'category_id' => 1,
			'author_id' => 1,
			'title' => 'Another Title',
			'content' => "\n\0<>!?$%';SELECT * FROM `secrit`;&^\"'\'<|24ZY <><4|24<T3|2Z",
			'time' => time()));
		$this->assertStrict($this->table->insert($post), 2);
		$this->assertEqual($this->table->find(array('type' => 'first', 'conditions' => array('id' => 2))), $post);
		
	}
	
}

?>