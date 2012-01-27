<?php
abstract class TestCase
{
	const TEST_METHOD_PREFIX = "test_";
	protected $temporal_result;
	
	public function pass()
	{
		$this->temporal_result->add_assertion(true);
		return true;
	}
	
	public function fail($message)
	{
		$exception = new Exception($message);
		$this->store_exception($exception);
		return false;
	}
	
	protected function store_exception(Exception $exception)
	{
		$this->temporal_result->add_assertion(false, $exception);
	}
	
	public function assert_true($prove)
	{
		if($prove === true) return $this->pass();
			
		return $this->fail("Failed asserting true for {$this->variable_dump($prove)}");
	}
	
	public function assert_false($prove)
	{
		if($prove === false) return $this->pass();
			
		return $this->fail("Failed asserting false for {$this->variable_dump($prove)}");
	}
	
	public function assert_equals($expected, $prove) 
	{
		$fail_message = "Failed asserting that expected:\n".$this->variable_dump($expected)
					."\nequals given:\n".$this->variable_dump($prove);
					
		if((is_bool($prove) OR is_bool($expected)) AND ($expected !== $prove))
			return $this->fail($fail_message);
			
		if($expected == $prove) return $this->pass();
		
		return $this->fail($fail_message);	
	}
	
	public function get_tests() 
	{	
		$reflected_self = new ReflectionClass($this);
		
		$tests = array();
		foreach($reflected_self->getMethods() as $one_method) 
		{	
			$method_name = $one_method->name;
			if($this->is_test($method_name)) $tests[] = $method_name;
		}
		
		return $tests;
	}
	
	protected function is_test($method_name)
	{
		return (substr($method_name, 0, 5) == self::TEST_METHOD_PREFIX);
	}
	
	public function run()
	{
		$results = array();
		
		foreach($this->get_tests() as $one_test)
		{
			$results[$one_test] = $this->run_one($one_test);
		}

		return $results;
	}
	
	public function run_one($test_name)
	{
		$this->temporal_result = new TestResult($test_name);
		try
		{
			$this->$test_name();
		}
		catch(Exception $exception)
		{
			$this->store_exception($exception);
		}

		return $this->temporal_result;
	}
	
	protected function variable_dump($subject)
	{
		ob_start();
			var_dump($subject);
			$result = ob_get_contents();
		ob_end_clean();
		
		return $result;
	}
}