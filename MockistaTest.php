<?php

use Mockista\MethodInterface;

require_once __DIR__ . "/bootstrap.php";

class MockistaTestException extends Exception
{
}


/**
 *
 * @author Jiri Knesl
**/
class MockistaTest extends PHPUnit_Framework_TestCase
{
	/**
	 * @var Mockista
	 */
	private $object;

	/**
	 * 
	 */
	protected function setUp()
	{
		$this->object = Mockista\mock();
	}

	/**
	 * 
	 */
	public function testAttribute()
	{
		$this->object->abc = 11;
		$this->assertEquals(11, $this->object->abc);
	}

	public function testMethod()
	{
		$method = $this->object->abc();
		$this->assertTrue($method instanceof MethodInterface);
	}

	public function testMethodReturn()
	{
		$this->object->abc()->andReturn(11);
		$this->object->freeze();
		$this->assertEquals(11, $this->object->abc());
	}


	public function testMethodCallback()
	{
		$this->object->abc('aaa')->andCallback(function($name){return strtoupper($name);});
		$this->object->freeze();
		$this->assertEquals('AAA', $this->object->abc('aaa'));
	}
	
	/**
	 * @expectedException MockistaTestException
	 */
	public function testMethodThrow()
	{
		$this->object->abc()->andThrow(new MockistaTestException);
		$this->object->freeze();
		$this->object->abc();
	}

	/**
	 * @expectedException Exception
	 * @expectedExceptionCode 1
	 */
	public function testCollectNever()
	{
		$this->object->abc()->never();
		$this->object->freeze()->abc();
		$this->object->assertExpectations();
	}

	public function testCollectExactly()
	{
		$this->object->abc()->exactly(3);
		$this->object->freeze()->abc();
		$this->object->abc();
		$this->object->abc();
		$this->object->assertExpectations();
	}

	/**
	 * @expectedException Exception
	 * @expectedExceptionCode 1
	 */
	public function testCollectExactlyBad()
	{
		$this->object->abc()->exactly(2);
		$this->object->freeze()->assertExpectations();
	}

	public function testCollectAtLeast()
	{
		$this->object->abc()->atLeast(2);
		$this->object->freeze()->abc();
		$this->object->abc();
		$this->object->abc();
		$this->object->assertExpectations();
	}

	/**
	 * @expectedException Exception
	 * @expectedExceptionCode 2
	 */
	public function testCollectAtLeastBad()
	{
		$this->object->abc()->atLeast(2);
		$this->object->freeze()->assertExpectations();
	}

	public function testCollectNoMoreThan()
	{
		$this->object->abc()->noMoreThan(3);
		$this->object->freeze()->abc();
		$this->object->abc();
		$this->object->abc();
		$this->object->assertExpectations();
	}


	/**
	 * @expectedException Exception
	 * @expectedExceptionCode 3
	 */
	public function testCollectNoMoreThanOnceAttribute()
	{
		$this->object->abc()->noMoreThanOnce;
		$this->object->freeze()->abc();
		$this->object->abc();
		$this->object->assertExpectations();
	}

	/**
	 * @expectedException Exception
	 * @expectedExceptionCode 3
	 */
	public function testCollectNoMoreThanBad()
	{
		$this->object->abc()->noMoreThan(2);
		$this->object->freeze()->abc();
		$this->object->abc();
		$this->object->abc();
		$this->object->assertExpectations();
	}

	public function testMultipleCalls()
	{
		$this->object->abc(1)->andReturn(2);
		$this->object->abc(2)->andReturn(3);
		$this->object->abc()->andReturn(4);
		$this->object->freeze();

		$this->assertEquals(2, $this->object->abc(1));
		$this->assertEquals(3, $this->object->abc(2));
		$this->assertEquals(4, $this->object->abc());
		$this->assertEquals(4, $this->object->abc('aa'));
	}

	public function testMockArgs()
	{
		$mock = Mockista\mock(array("x"=>11, "y"=>function($a){return $a * 2;}));
		$mock->freeze();
		$this->assertEquals(11, $mock->x);
		$this->assertEquals(4, $mock->y(2));
	}
}
