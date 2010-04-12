<?php

namespace BenchmarkTest;
use MVCWebComponents\UnitTest\UnitTest,
	MVCWebComponents\Benchmark;

class BenchmarkTest extends UnitTest {
	
	public function TestBenchmark() {
		
		Benchmark::start('test');
		$this->assertTrue(Benchmark::started('test'));
		
		$this->assertTrue(is_float(Benchmark::end('test')));
		$this->assertTrue(Benchmark::finished('test'));
		$this->assertTrue(is_float(Benchmark::read('test')));
		$this->assertTrue(Benchmark::read('test') > 0);
		
		$this->assertStrict(Benchmark::summary(), array('test' => Benchmark::read('test')));
		
		Benchmark::reset('test');
		$this->assertFalse(Benchmark::started('test'));
		$this->assertFalse(Benchmark::finished('test'));
		
	}
	
	public function TestCombine() {
		
		Benchmark::start('test1');
		sleep(0.01);
		Benchmark::end('test1');
		
		Benchmark::start('test2');
		sleep(0.01);
		Benchmark::end('test2');
		
		$this->assertStrict(Benchmark::combine('test', 'test'), Benchmark::read('test1') + Benchmark::read('test2'));
		$this->assertStrict(Benchmark::combine('test', array('test1','test2')), Benchmark::read('test1') + Benchmark::read('test2'));
		
	}
	
}

?>