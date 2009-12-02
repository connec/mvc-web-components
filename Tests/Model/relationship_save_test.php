<?php

error_reporting(E_ALL);

include 'common.php';
use MVCWebComponents\Database\Database as Database, MVCWebComponents\Model\Model as Model, MVCWebComponents\UnitTest as UnitTest;

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

class RelationshipSaveTest extends UnitTest {
	
	// Initiate the models
	public function preTesting() {
		
		$return[] = $this->user = User::getInstance();
		$return[] = $this->post = Post::getInstance();
		return ($return[0] and $return[1]);
		
	}
	
	// Test cascading works for belongsTo relations...
	public function TestBelongsTo() {
		
		// Create an author...
		$user = (object)array(
			'name' => 'User Name',
			'password' => 'password',
			'user_group_id' => 1,
			'joined' => time());
		
		// Create a post
		$post = (object)array(
			'category_id' => 1,
			'title' => 'Post Title',
			'content' => 'Post Content',
			'time' => time(),
			'Author' => $user);
		
		// Save it...
		$this->assertTrue($this->post->save($post, array('cascade' => true)));
		
		// Check everything's there...
		unset($post->Author);
		$this->assertEqual($post, $this->post->findFirst(array('cascade' => false)));
		$this->assertEqual($user, $this->user->findFirst(array('cascade' => false, 'orderBy' => 'id desc')));
		
	}
	
	// Test hasMany...
	public function TestHasMany() {
		
		// Get our user/post from the DB
		$this->assertEqual($user = $this->user->findFirst(array('cascade' => false, 'orderBy' => 'id desc')), true);
		$this->assertEqual($post1 = $this->post->findFirst(array('cascade' => false)), true);
		
		// Let's add another post...
		$post2 = clone $post1;
		unset($post2->id);
		$post2->title = 'Another Title';
		
		// And alter the original one a bit...
		$post1->content = 'NON ORIGINAL CONTENTZ';
		
		// Assign them to the user and save
		$user->Posts = array($post1, $post2);
		$this->assertTrue($this->user->save($user, array('cascade' => true)));
		
		// Again, double check everything's been saved...
		unset($user->Posts);
		$this->assertEqual($user, $this->user->findFirst(array('cascade' => false, 'orderBy' => 'id desc')));
		$this->assertEqual($post1, $this->post->findFirstById($post1->id, array('cascade' => false)));
		$this->assertEqual($post2, $this->post->findFirstById($post2->id, array('cascade' => false)));
		
	}
	
	// Cleanup after...
	public function postTesting() {
		
		$return[] = $this->user->delete($this->user->findFirst(array('orderBy' => 'id desc')));
		$return[] = Database::query('truncate table `posts`');
		return ($return[0] and $return[1]);
		
	}
	
}

new RelationshipSaveTest;

?>