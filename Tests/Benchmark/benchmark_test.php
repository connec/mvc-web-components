<?php

namespace BenchmarkTest;
use MVCWebComponents\UnitTest\UnitTest,
	MVCWebComponents\Benchmark;

class BenchmarkTest extends UnitTest {
	
	public function TestBenchmark() {
		
		Benchmark::start('test');
		$this->assertTrue(Benchmark::check('test_start'));
		$this->assertTrue(Benchmark::started('test'));
		
		$this->assertTrue(is_float(Benchmark::end('test')));
		$this->assertTrue(Benchmark::check('test_end'));
		$this->assertTrue(Benchmark::check('test_total'));
		$this->assertFalse(Benchmark::check('test'));
		$this->assertTrue(is_float(Benchmark::read('test')));
		$this->assertTrue(Benchmark::read('test') > 0);
		$this->assertTrue(Benchmark::ended('test'));
		
		$this->assertStrict(Benchmark::summary(), array('test' => Benchmark::read('test')));
		
		Benchmark::reset('test');
		$this->assertFalse(Benchmark::started('test'));
		$this->assertFalse(Benchmark::ended('test'));
		$this->assertFalse(Benchmark::check('test_total'));
		
	}
	
}

?>