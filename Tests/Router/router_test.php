<?php

namespace RouterTest;
use MVCWebComponents\UnitTest\UnitTest,
	MVCWebComponents\Router;

class RouterTest extends UnitTest {
	
	public function preTest() {
		
		Router::disconnectAll();
		
	}
	
	public function TestPlainParameters() {
		
		Router::connect('/', array('p1' => 1, 'p2' => 2));
		$params = Router::route('/');
		$this->assertStrict($params['p1'], 1);
		$this->assertStrict($params['p2'], 2);
		
		Router::connect('/a/b/c', array('p1' => 'a', 'p2' => 'b', 'p3' => 'c'));
		$params = Router::route('/a/b/c');
		$this->assertStrict($params['p1'], 'a');
		$this->assertStrict($params['p2'], 'b');
		$this->assertStrict($params['p3'], 'c');
		
	}
	
	public function TestExtraParameters() {
		
		Router::connect('/', array('a' => 1, 'b'));
		$params = Router::route('/');
		$this->assertStrict($params['a'], 1);
		$this->assertStrict($params['other'], array('b'));
		
	}
	
	public function TestVariableParameters() {
		
		Router::connect('/:pOne/:pTwo/:pThree', array('p1' => ':pOne', 'p2' => ':pTwo', 'p3' => ':pThree'));
		$params = Router::route('/a/b/c');
		$this->assertStrict($params['p1'], 'a');
		$this->assertStrict($params['p2'], 'b');
		$this->assertStrict($params['p3'], 'c');
		
		Router::connect('/hello_:name', array('name' => 'hello_:name'));
		$params = Router::route('/hello_world');
		$this->assertStrict($params['name'], 'hello_world');
		
	}
	
	public function TestWildCard() {
		
		Router::connect('/*', array('page' => 'home'));
		$params = Router::route('/a/b/c');
		$this->assertStrict($params['page'], 'home');
		$this->assertStrict($params['other'], array('a','b','c'));
		
	}
	
	public function TestMultipleAssignment() {
		
		Router::connect(array('/', '/home'), array('page' => 'home'));
		$params = Router::route('/');
		$this->assertStrict($params['page'],'home');
		$params = Router::route('/home');
		$this->assertStrict($params['page'],'home');
		
	}
	
	public function TestParameterInference() {
		
		Router::connect('/:a/:b/:c');
		$params = Router::route('/1/2/3/');
		$this->assertStrict($params['a'], '1');
		$this->assertStrict($params['b'], '2');
		$this->assertStrict($params['c'], '3');
		
		try {
			$params = Router::route('/1/2/3/a/b/c');
		} catch(\MVCWebComponents\NoConnectionException $e) {
			$a = true;
		}
		$this->assertTrue($a);
		
		Router::connect('/:a/:b/:c/*');
		$params = Router::route('/1/2/3/a/b/c');
		$this->assertStrict($params['a'], '1');
		$this->assertStrict($params['b'], '2');
		$this->assertStrict($params['c'], '3');
		$this->assertStrict($params['other'], array('a', 'b', 'c'));
		
	}
	
	public function TestQueryString() {
		
		Router::connect('/:a/:b');
		$params = Router::route('/1/2?c=3&d=4');
		$this->assertStrict($params['a'], '1');
		$this->assertStrict($params['b'], '2');
		$this->assertStrict($params['other'], array('c' => '3', 'd' => '4'));
		
		Router::connect('/:a/:b/*', array(5));
		$params = Router::route('/1/2/3/4?c=3&d=4');
		$this->assertStrict($params['a'], '1');
		$this->assertStrict($params['b'], '2');
		$this->assertStrict($params['other'], array(5, '3', '4', 'c' => '3', 'd' => '4'));
		
	}
	
}

?>