<?php

namespace HookTest;
use MVCWebComponents\UnitTest\UnitTest, MVCWebComponents\Model\Model;

class Post extends Model {
	
	protected static $afterConstruct = array('setStringTime');
	protected static $beforeSave = array('setTime');
	
	public $stringTime = '';
	
	protected function setStringTime() {
		
		$this->stringTime = date('Y-m-d H:i:s', $this->time);
		
	}
	
	protected function setTime() {
		
		$this->time = time();
		
	}
	
}

class HookTest extends UnitTest {
	
	public $dependencies = array('RelationshipSaveTest', 'RelationshipFindTest', 'ValidationTest');
	
	public function TestConstructHooks() {
		
		$post = new Post(array('time' => time()));
		$this->assertStrict($post->stringTime, date('Y-m-d H:i:s', $post->time));
		
	}
	
	public function TestSaveHooks() {
		
		$post = new Post(array(
			'category_id' => 1,
			'author_id' => 1,
			'title' => 'Post Title',
			'content' => 'Post Content'));
		$post->save();
		
	}
	
}

?>