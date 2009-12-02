<?php

include 'common.php';
use MVCWebComponents\Database\Database, MVCWebComponents\Model\Model, MVCWebComponents\UnitTest;

class User extends Model {
	
	protected $hasMany = array(
		'Post' => array(
			'foreignKey' => 'author_id'
		)
	);
	
}

class Post extends Model {
	
	protected $belongsTo = array(
		'Author' => array(
			'model' => 'User',
		)
	);
	
}

class User2 extends Model {
	
	protected $tableName = 'users';
	
	protected $hasOne = array(
		'Post2' => array(
			'model' => 'Post2',
			'foreignKey' => 'author_id'
		)
	);
	
}

class Post2 extends Model {
	
	protected $tableName = 'posts';
	
	protected $belongsTo = array(
		'Author2' => array(
			'model' => 'User2',
			'foreignKey' => 'author_id'
		)
	);
	
}

class RelationshipFindTest extends UnitTest {
	
	# Create and save some posts.
	public function preTesting() {
		
		$return = array();
		$return[] = $this->userModel = User::getInstance();
		$return[] = $this->postModel = Post::getInstance();
		$return[] = $this->user2Model = User2::getInstance();
		$return[] = $this->post2Model = Post2::getInstance();
		
		$post1 = new StdClass;
		$post1->category_id = 1;
		$post1->author_id = 1;
		$post1->title = 'Post 1';
		$post1->content = 'Post 1 Content';
		$post1->time = time();
		
		$post2 = new StdClass;
		$post2->category_id = 1;
		$post2->author_id = 1;
		$post2->title = 'Post 2';
		$post2->content = 'Post 2 Content';
		$post2->time = time();
		
		$post3 = new StdClass;
		$post3->category_id = 1;
		$post3->author_id = 2;
		$post3->title = 'Post 3';
		$post3->content = 'Post 3 Content';
		$post3->time = time();

		$return[] = $this->postModel->save($post1);
		$return[] = $this->postModel->save($post2);
		$return[] = $this->postModel->save($post3);
		
		return $return[0] and $return[1] and $return [2] and $return[3] and $return[4] and $return[5] and $return [6];
		
	}
	
	# Test hasMany relations.
	public function TestHasMany() {
		
		# Assert the user with name 'Bob' is found.
		$this->assertEqual($bob = $this->userModel->findFirstByName('Bob'), true);
		
		# Assert $bob has 2 posts.
		$this->assertEqual(count($bob->Posts), 2);
		
		# Assert the first post in $bob is Bob's first post in the DB
		$this->assertEqual($bob->Posts[0], $this->postModel->findFirstByAuthorId($bob->id, array('cascade' => false)));
		
	}
	
	# Test hasOne relations.
	public function TestHasOne() {
		
		# Assert the user with name 'Bob' is found.
		$this->assertEqual($bob = $this->user2Model->findFirstByName('Bob'), true);
		
		# Assert $bob->Post is a StdClass.
		$this->assertTrue($bob->Post2 instanceof StdClass);
		
		# Finally, assert the post is the first one attached to Bob.
		$this->assertEqual($bob->Post2, $this->post2Model->findFirstByAuthorId($bob->id, array('cascade' => false)));
		
	}
	
	# Test belongsTo relations.
	public function TestBelongsTo() {
		
		# Assert that we have a post.
		$this->assertEqual($post = $this->postModel->findFirst(), true);
		
		# Assert that $post->Author is a StdClass
		$this->assertTrue($post->Author instanceof StdClass);
		
		# Finally, assert the id's match.
		$this->assertStrict($post->Author->id, $post->author_id);
		
	}
	
	# Truncate posts when we're done.
	public function postTesting() {
		
		return Database::query('truncate table `posts`');
		
	}
	
}

new RelationshipFindTest;

?>