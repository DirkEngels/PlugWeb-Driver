<?php

namespace Plugwise\Driver;

/**
 * 
 * Unit Test for Plugwise Driver Request
 * @author dirk
 *
 */
class DeviceTest extends \PHPUnit_Framework_Testcase {
    protected $_device;

    protected function setUp() {
        $this->_device = new \PlugWeb\Driver\Device('123456');
    }

    protected function tearDown() {
        unset($this->_device);
    }

    /*
     * Unit Tests
     */
    public function testConstructorSingleArgument() {
        $this->assertEquals('123456', $this->_device->getMac());
        $this->assertEquals('/dev/ttyUSB0', $this->_device->getDevice());
    }

    public function testConstructorTwoArguments() {
        $this->_device = new \PlugWeb\Driver\Device('654321', '/dev/ttyUSB1');
        $this->assertEquals('654321', $this->_device->getMac());
        $this->assertEquals('/dev/ttyUSB1', $this->_device->getDevice());
    }

    public function testSetMac() {
        $this->assertEquals('123456', $this->_device->getMac());
        $this->_device->setMac('654321');
        $this->assertEquals('654321', $this->_device->getMac());
    }

    public function testSetDevice() {
        $this->assertEquals('/dev/ttyUSB0', $this->_device->getDevice());
        $this->_device->setDevice('/dev/ttyUSB1');
        $this->assertEquals('/dev/ttyUSB1', $this->_device->getDevice());
    }

    public function testSetSerial() {
        $this->markTestIncomplete(
          'Have to find out how to unit test this nicely!'
        );
/*
        $this->assertInstanceOf('PlugWeb\Driver\Serial', $this->_device->getSerial());

        $serial = new \PlugWeb\Driver\Serial();
        $this->_device->setSerial($serial);
        $this->assertInstanceOf('PlugWeb\Driver\Serial', $this->_device->getSerial());
        $this->assertEquals($serial, $this->_device->getSerial());
*/
    }

    public function testSetRequest() {
        $this->assertInstanceOf('PlugWeb\Driver\Request', $this->_device->getRequest());

        $request = new \PlugWeb\Driver\Request();
        $this->_device->setRequest($request);
        $this->assertInstanceOf('PlugWeb\Driver\Request', $this->_device->getRequest());
        $this->assertEquals($request, $this->_device->getRequest());
    }

    public function testSetResponse() {
        $this->assertInstanceOf('PlugWeb\Driver\Response', $this->_device->getResponse());

        $response = new \PlugWeb\Driver\Response();
        $this->_device->setResponse($response);
        $this->assertInstanceOf('PlugWeb\Driver\Response', $this->_device->getResponse());
        $this->assertEquals($response, $this->_device->getResponse());
    }

    public function testPowerSwitchOn() {
        $this->markTestIncomplete(
          'Have to find out how to unit test this nicely!'
        );
/*
        $serial = $this->getMock('PlugWeb\Driver\Serial', array('powerSwitchOn'), array('123456'));
        $serial->expects($this->once())
            ->method('sendData')
            ->with($this->equalTo('teststring'));

        $this->_device->setSerial($serial);
*/
    }

}