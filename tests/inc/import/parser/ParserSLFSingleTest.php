<?php

/**
 * Generated by PHPUnit_SkeletonGenerator 1.2.0 on 2013-04-07 at 17:50:49.
 */
class ParserSLFSingleTest extends PHPUnit_Framework_TestCase {

	/**
	 * @var ParserTCXSingle
	 */
	protected $object;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp() { }

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 */
	protected function tearDown() { }

	public function testWrongXML() {
		$XML = simplexml_load_string('<test>abc</test>');
		$Parser = new ParserSLFSingle('', $XML);
		$Parser->parse();

		$this->assertTrue( $Parser->failed() );
	}

	public function testNoEntries() {
		$XML = simplexml_load_string('<Log><LogEntries></LogEntries></Log>');
		$Parser = new ParserSLFSingle('', $XML);
		$Parser->parse();

		$this->assertTrue( $Parser->failed() );
	}

}
