<?php

include 'common.php';
use MVCWebComponents\Model\Model as Model, MVCWebComponents\UnitTest as UnitTest;

class User extends Model {}

class UpdateTest extends UnitTest {
	
	public function preTesting() {
		
		return $this->user = User::getInstance();
		
	}
	
	public function TestUpdate() {
		
		$original = $this->user->findFirst();
		
		$new = clone $original;
		$new->name = 'LORLNAME';
		
		$this->assertTrue($this->user->save($new));
		$this->assertEqual($new, $this->user->findFirst(array('cascade' => false)));
		
		$this->assertTrue($this->user->save($original));
		$this->assertEqual($original, $this->user->findFirst(array('cascade' => false)));
		
	}
	
}

new UpdateTest;

?>