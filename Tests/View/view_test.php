<?php

namespace ViewTest;
use MVCWebComponents\UnitTest\UnitTest,
	MVCWebComponents\View,
	MVCWebComponents\MissingTemplateException;

class Helper {
	
	private $function = 'strtoupper';
	
	public function __construct($options = array()) {
		
		if(isset($options['function']) and $options['function'] == 'strtolower')
			$this->function = 'strtolower';
		
	}
	
	public function changecase($a) {
		
		$func = $this->function;
		return $func($a);
		
	}
	
}

class ViewTest extends UnitTest {
	
	public function TestView() {
		
		View::addPrePath('tpl');
		View::addPostPath('php');
		$view = new View('.');
		$this->assertStrict($view->getTemplate(), 'tpl.php');
		
		$this->assertTrue($view->checkTemplate());
		
		$shouldbe = <<<STRING
<h1>a</h1>
<p>
	tpl.php</p>
STRING;
		$view->header = 'a';
		$result = $view->render(true);
		$this->assertStrict($result, $shouldbe);
		
		try {
			$view = new View('not a file');
		}catch(MissingTemplateException $e) {
			$a = true;
		}
		$this->assertTrue($a);
		
	}
	
	public function TestImportHelper() {
		
		$view = new View('.');
		$view->importHelper('ViewTest\\Helper');
		$this->assertStrict($view->Helper->changecase('test'), 'TEST');
		
		$view->importHelper('ViewTest\\Helper', array('function' => 'strtolower'));
		$this->assertStrict($view->Helper->changecase('TEST'), 'test');
		
	}
	
	public function TestGlobalAssign() {
		
		View::registerGlobal('header', 'a');
		
		$should_be = <<<STRING
<h1>a</h1>
<p>
	tpl.php</p>
STRING;
		$view = new View('.');
		$this->assertStrict($view->render(true), $should_be);
		
	}
	
	public function TestPartial() {
		
		View::addPrePath('');
		
		$view = new View('.');
		$view->header = View::partial('p.', array('content' => 'test'));
		
		$should_be = <<<STRING
<h1><p>test</p></h1>
<p>
	tpl.php</p>
STRING;
		
		$this->assertStrict($view->render(true), $should_be);
		
	}
	
}

?>