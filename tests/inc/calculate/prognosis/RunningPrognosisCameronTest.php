<?php

/**
 * Generated by PHPUnit_SkeletonGenerator 1.2.0 on 2013-11-16 at 18:06:05.
 */
class RunningPrognosisCameronTest extends PHPUnit_Framework_TestCase {

	/**
	 * @var RunningPrognosisCameron
	 */
	protected $object;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp() {
		$this->object = new RunningPrognosisCameron;
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 */
	protected function tearDown() {
		
	}

	/**
	 * @covers RunningPrognosisCameron::setupFromDatabase
	 */
	public function testSetupFromDatabase() {
		DB::getInstance()->insert('training', array('sportid', 'vdot_by_time', 's', 'distance'), array(CONF_RUNNINGSPORT, 60, 16*60 + 32, 5) );
		DB::getInstance()->insert('training', array('sportid', 'vdot_by_time', 's', 'distance'), array(CONF_RUNNINGSPORT, 45, 90*60 +  0, 21.1) );

		$this->object->setupFromDatabase();

		$this->assertEquals(            9*60 + 33, $this->object->inSeconds(3), '', 1 );
		$this->assertEquals(           16*60 + 32, $this->object->inSeconds(5), '', 1 );
		$this->assertEquals(           34*60 + 26, $this->object->inSeconds(10), '', 1 );
		$this->assertEquals( 1*60*60 + 15*60 + 56, $this->object->inSeconds(21.1), '', 1 );
		$this->assertEquals( 2*60*60 + 41*60 + 22, $this->object->inSeconds(42.2), '', 1 );

		DB::getInstance()->exec('TRUNCATE TABLE `runalyze_training`');
	}

	/**
	 * @covers RunningPrognosisCameron::setReferenceResult
	 * @covers RunningPrognosisCameron::inSeconds
	 */
	public function testSimplePrognosis() {
		$this->object->setReferenceResult(2, (14*60 + 20));

		$this->assertEquals( 22.357*60, $this->object->inSeconds(3), '', 0.1 );
	}

	/**
	 * @covers RunningPrognosisCameron::setReferenceResult
	 * @covers RunningPrognosisCameron::inSeconds
	 */
	public function testMyCurrentResult() {
		$this->object->setReferenceResult(5, (16*60 + 32));

		$this->assertEquals(            9*60 + 33, $this->object->inSeconds(3), '', 1 );
		$this->assertEquals(           16*60 + 32, $this->object->inSeconds(5), '', 1 );
		$this->assertEquals(           34*60 + 26, $this->object->inSeconds(10), '', 1 );
		$this->assertEquals( 1*60*60 + 15*60 + 56, $this->object->inSeconds(21.1), '', 1 );
		$this->assertEquals( 2*60*60 + 41*60 + 22, $this->object->inSeconds(42.2), '', 1 );
	}

}
