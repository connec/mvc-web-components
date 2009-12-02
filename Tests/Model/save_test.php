<?php

include 'common.php';

use MVCWebComponents\Model\Model as Model, MVCWebComponents\UnitTest as UnitTest, MVCWebComponents\Database\Database as Database;

class Post extends Model {}

class SaveTest extends UnitTest {
	
	// Initiate our Post model.
	public function preTesting() {
		
		return $this->model = Post::getInstance();
		
	}
	
	// Test insertion when record not in DB
	public function TestInserts() {
		
		// Create some posts...
		$post1 = (object)array(
			'category_id' => 1,
			'author_id' => 1,
			'title' => 'Post Title',
			'content' => 'Post Content',
			'time' => time());
		
		$post2 = (object)array(
			'category_id' => 1,
			'author_id' => 1,
			'title' => 'Another Title',
			'content' => 'More Content...',
			'time' => time());
		
		// Ensure they're inserted (i.e. the primary key is set)
		$this->assertTrue($this->model->save($post1));
		$this->assertEqual($post1->id, true);
		$this->assertTrue($this->model->save($post2));
		$this->assertEqual($post2->id, true);
		
		// Double check by comparing them with database results...
		$this->assertEqual($post1, $this->model->findFirstById($post1->id));
		$this->assertEqual($post2, $this->model->findFirstById($post2->id));
		
	}
	
	// Test updating...
	public function TestUpdating() {
		
		$this->assertEqual($post = $this->model->findFirst(), true);
		
		$post->title = 'Updated Title.';
		$this->assertTrue($this->model->save($post));
		
		// Check it updated...
		$this->assertEqual($this->model->findFirst(), $post);
		
	}
	
	// Cleanup the table afterwards.
	public function postTesting() {
		
		return Database::query('truncate table `posts`');
		
	}
	
}

new SaveTest;

?>