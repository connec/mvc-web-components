<?php

namespace RelationshipFindTest;
use MVCWebComponents\Model\Model, MVCWebComponents\Database\Database, MVCWebComponents\UnitTest\UnitTest;

class User extends Model {
	
	protected static $hasMany = array('Post' => array('foreignKey' => 'author_id'));
	
}

class Post extends Model {
	
	protected static $belongsTo = array(
		'Author' => array(
			'model' => 'User'
		)
	);
	
}

class User2 extends Model {
	
	protected static $tableName = 'users';
	
	protected static $hasOne = array(
		'Post' => array(
			'model' => 'Post2',
			'foreignKey' => 'author_id',
			'options' => array('orderBy' => 'id desc')
		)
	);
	
}

class Post2 extends Model {
	
	protected static $tableName = 'posts';
	
	protected static $belongsTo = array('Author' => array('model' => 'User2'));
	
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
		$this->assertEqual($users = User::findAll(array('orderBy' => 'id desc')), true);
		$this->assertEqual($users[0]->Posts, array($this->post2));
		$this->assertEqual($users[1]->Posts, array($this->post1, $this->post3));
		
	}
	
	public function TestHasOne() {
		
		// Use our alternative 'User2' to test hasOne.
		$this->assertEqual($users = User2::findAll(), true);
		$this->assertEqual($users[0]->Post, $this->post3);
		$this->assertEqual($users[1]->Post, $this->post2);
		
	}
	
	public function TestBelongsTo() {
		
		// Find the posts and check the user is correct.
		$this->assertEqual($posts = Post::findAll(), Post2::findAll());
		$this->assertEqual(
			array_map(function($post) {return (object)array_filter((array)$post, function($x) {return !is_object($x);});}, $posts),
			array($this->post1, $this->post2, $this->post3));
		$this->assertEqual($posts[0]->Author, User::findFirstById(1, array('cascade' => false)));
		$this->assertEqual($posts[1]->Author, User::findFirstById(2, array('cascade' => false)));
		$this->assertEqual($posts[2]->Author, User::findFirstById(1, array('cascade' => false)));
		
	}
	
}

?>