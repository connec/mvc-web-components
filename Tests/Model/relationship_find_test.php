<?php

namespace RelationshipFindTest;
use MVCWebComponents\Model\Model, MVCWebComponents\Database\Database, MVCWebComponents\UnitTest;

class User extends Model {
	
	protected static $hasMany = array('Post' => array('foreignKey' => 'author_id', 'model' => 'Post'));
	
}

class Post extends Model {
	
	protected static $belongsTo = array(
		'Author' => array(
			'model' => 'User'
		)
	);
	
}

class RelationshipFindTest extends UnitTest {
	
	public $dependencies = array('FindTest', 'CallStaticTest', 'InitTest', 'InsertTest');
	
	public function preTesting() {
		
		// Put some posts in...
		$this->post1 = (object)array(
			'category_id' => 1,
			'author_id' => 1,
			'title' => 'Post 1 Title',
			'content' => 'Post 1 Content',
			'time' => time());
		$this->post2 = (object)array(
			'category_id' => 1,
			'author_id' => 2,
			'title' => 'Post 2 Title',
			'content' => 'Post 2 Content',
			'time' => time());
		$this->post3 = (object)array(
			'category_id' => 1,
			'author_id' => 1,
			'title' => 'Post 3 Title',
			'content' => 'Post 3 Content',
			'time' => time());
		
		$this->assertTrue(Post::insert($this->post1));
		$this->assertTrue(Post::insert($this->post2));
		$this->assertTrue(Post::insert($this->post3));
		
		return true;
		
	}
	
	public function TestHasMany() {
		
		// Find the users and check the posts are correct.
		$this->assertEqual($user = User::findAll(array('orderBy' => 'id desc')), true);
		$this->assertEqual($user[0]->Posts, array($this->post2));
		$this->assertEqual($user[1]->Posts, array($this->post1, $this->post3));
		
	}
	
	public function postTesting() {
		
		$this->assertTrue(Database::query('truncate table `posts`'));
		return true;
		
	}
	
}

?>