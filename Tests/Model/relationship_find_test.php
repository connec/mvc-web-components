<?php

namespace RelationshipFindTest;
use MVCWebComponents\Model\Model, MVCWebComponents\Database\Database, MVCWebComponents\UnitTest;

class User extends Model {
	
	protected static $hasMany = array('Post' => array('foreignKey' => 'author_id', 'model' => '\\RelationshipFindTest\\Post'));
	
}

class Post extends Model {
	
	protected static $belongsTo = array(
		'Author' => array(
			'model' => '\\RelationshipFindTest\\User'
		)
	);
	
}

class RelationshipFindTest extends UnitTest {
	
	public $dependencies = array('FindTest', 'CallStaticTest', 'InitTest', 'InsertTest');
	
	public function preTesting() {
		
		// Put some posts in...
		$post1 = (object)array(
			'category_id' => 1,
			'author_id' => 1,
			'title' => 'Post 1 Title',
			'content' => 'Post 1 Content',
			'time' => time());
		$post2 = (object)array(
			'category_id' => 1,
			'author_id' => 2,
			'title' => 'Post 2 Title',
			'content' => 'Post 2 Content',
			'time' => time());
		$post3 = (object)array(
			'category_id' => 1,
			'author_id' => 1,
			'title' => 'Post 3 Title',
			'content' => 'Post 3 Content',
			'time' => time());
		
		$this->assertTrue(Post::insert($post1));
		$this->assertTrue(Post::insert($post2));
		$this->assertTrue(Post::insert($post3));
		
		return true;
		
	}
	
	public function TestFind() {
		
		var_dump(User::findAll());
		
	}
	
	public function postTesting() {
		
		$this->assertTrue(Database::query('truncate table `posts`'));
		return true;
		
	}
	
}

?>