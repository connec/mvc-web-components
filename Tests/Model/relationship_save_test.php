<?php

namespace RelationshipSaveTest;
use MVCWebComponents\Model\Model, MVCWebComponents\Database\Database, MVCWebComponents\UnitTest;

class User extends Model {
	
	protected static $hasMany = array('Post' => array('foreignKey' => 'author_id'));
	
}

class Post extends Model {
	
	protected static $belongsTo = array('Author' => array('model' => 'User'));
	
}

class User2 extends Model {
	
	protected static $tableName = 'users';
	
	protected static $hasOne = array('Post' => array('model' => 'Post2', 'foreignKey' => 'author_id'));
	
}

class Post2 extends Model {
	
	protected static $tableName = 'posts';
	
	protected static $belongsTo = array('Author' => array('model' => 'User2'));
	
}

class RelationshipSaveTest extends UnitTest {
	
	public $dependencies = array('SaveTest', 'RelationshipFindTest');
	
	public function TestHasOneInsert() {
		
		$user = User2::findFirst();
		$user->Post = (object)array(
			'category_id' => 1,
			'title' => 'Bob\' Post!',
			'content' => 'Content...',
			'time' => time());
		$this->assertTrue(User2::save($user, array('cascade' => true)));
		$this->assertEqual($user->Post->author_id, $user->id);
		$this->assertEqual(Post2::findFirst(array('cascade' => false)), $user->Post);
		
	}
	
	public function TestHasOneUpdate() {
		
		$user = User2::findFirst();
		$user->Post->title = 'New Title!';
		$this->assertTrue(User2::save($user, array('cascade' => true)));
		$this->assertEqual(Post2::findFirst()->title, 'New Title!');
		
	}
	
	public function TestBelongsToInsert() {
		
		$post = Post2::findFirst(array('cascade' => false));
		$post->Author = (object)array(
			'name' => 'Delete',
			'password' => 'whatever',
			'user_group_id' => 1,
			'joined' => time());
		$this->assertTrue(Post2::save($post, array('cascade' => true)));
		$this->assertEqual($post->author_id, $post->Author->id);
		$this->assertEqual(User2::findFirst(array('cascade' => false, 'orderBy' => 'id desc')), $post->Author);
		
	}
	
	public function TestBelongsToUpdate() {
		
		// Also test the updating/insertion is done in the right order:
		$post2 = Post2::findFirst();
		unset($post2->id);
		$post2->title = 'Updated Title';
		$this->assertTrue(Post2::save($post2, array('cascade' => true)));
		$this->assertFalse(Post2::findFirst(array('orderBy' => 'id desc')) == Post2::findFirst());
		$this->assertEqual(Post2::findFirst(array('orderBy' => 'id desc')), $post2);
		$this->assertEqual(Post2::findFirst(array('orderBy' => 'id desc'))->Author->id, $post2->author_id);
		
	}
	
	public function TestHasManyInsert() {
		
		$this->assertEqual($posts = Post::findAll(array('fields' => array('category_id','title','content','time'), 'cascade' => false)), true);
		// Reset everything for convenience.
		$this->assertTrue($this->postTesting());
		
		$user = User::findFirst();
		$user->Posts = $posts;
		$this->assertTrue(User::save($user, array('cascade' => true)));
		$this->assertEqual(User::findFirst(), $user);
		$this->assertEqual(User::findFirst()->Posts[0]->author_id, $user->id);
		
	}
	
	public function TestHasManyUpdate() {
		
		$this->assertEqual($user = User::findFirst(), true);
		$user->Posts[0]->title = 'New Title for 1st Post';
		$user->Posts[1]->content = 'Mayhaps some new content?';
		$this->assertTrue(User::save($user, array('cascade' => true)));
		$this->assertEqual(User::findFirst()->Posts[0]->title, 'New Title for 1st Post');
		$this->assertEqual(User::findFirst()->Posts[1]->content, 'Mayhaps some new content?');
		
	}
	
	public function postTesting() {
		
		return
			Database::query('truncate table `posts`') and
			Database::query('delete from `users` where `name` = \'Delete\'');
		
	}
	
}

?>