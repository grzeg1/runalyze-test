<?php

require_once dirname(__FILE__) . '/../../../../inc/html/formular/class.FormularInput.php';

/**
 * Test class for FormularInput.
 * Generated by PHPUnit on 2012-03-03 at 08:32:49.
 */
class FormularInputTest extends PHPUnit_Framework_TestCase {

	/**
	 * @var FormularInput
	 */
	protected $object;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp() {
		$this->object = new FormularInput('name', 'Label');
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 */
	protected function tearDown() {
		
	}

	/**
	 * @covers FormularInput::setStandardSize
	 * @todo Implement testSetStandardSize().
	 */
	public function testSetStandardSize() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
				'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers FormularInput::setSize
	 * @todo Implement testSetSize().
	 */
	public function testSetSize() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
				'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers FormularInput::displayField
	 * @todo Implement testDisplayField().
	 */
	public function testDisplayField() {
		// Must use OutputTestCase
		$this->markTestIncomplete(
				'This test has not been implemented yet.'
		);
	}

}

?>
