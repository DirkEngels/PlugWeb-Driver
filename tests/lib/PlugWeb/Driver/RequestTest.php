<?php

namespace Plugwise\Driver;

/**
 * 
 * Unit Test for Plugwise Driver Request
 * @author dirk
 *
 */
class RequestTest extends \PHPUnit_Framework_Testcase {
	protected $_request;
	
	protected function setUp() {
		$this->_request = new \PlugWeb\Driver\Request();
	}
	protected function tearDown() {
		unset($this->_request);
	}
	
	/*
	 * Unit Tests
	 */
	public function testActionInitStick() {
		$out = "\x05\x05\x03\x03000AB43C\x0d\x0a";
		
		$this->assertEquals($out, $this->_request->actionInitStick());
	}
	public function testActionSwitchOn() {
		$out = "\x05\x05\x03\x03000AB43C\x0d\x0a";
		
		$this->assertEquals($out, $this->_request->actionInitStick());
	}

	/**
	 * @dataProvider providerMakeCrcCheckSum
	 */
	public function testMakeCrcCheckSum($string, $expectedCrc) {
		
		$computedCrc = $this->_request->makeCrcCheckSum($string);
		
		$this->assertEquals($expectedCrc, $computedCrc);
	}

	/**
	 * Data provider for the makeCrcCheckSum 
	 */
	public function providerMakeCrcCheckSum() {
		return array(
			array('0017000D6F000072931001', 'BC88'),
			array('0017000D6F000072931000', 'ACA9'),
			array('0012000D6F0000729310', 'E61A'),
			array('000A', 'B43C')
		);
	}
}