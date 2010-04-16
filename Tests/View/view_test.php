<?php

namespace ViewTest;
use MVCWebComponents\UnitTest\UnitTest,
	MVCWebComponents\View,
	MVCWebComponents\MissingTemplateException;

class Test1Helper {
	public function changecase($a) { return strtoupper($a); }
}

class Test2Helper {
	public function changecase($a) { return strtolower($a); }
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
		
		View::addHelperNamespace('\\ViewTest\\');
		
		$view = new View('.');
		$view->importHelper('test1');
		$this->assertStrict($view->get('test1')->changecase('test'), 'TEST');
		
		$view->importHelper('test2');
		$this->assertStrict($view->get('test2')->changecase('TEST'), 'test');
		
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