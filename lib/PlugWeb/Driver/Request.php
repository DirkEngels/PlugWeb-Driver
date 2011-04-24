<?php

namespace PlugWeb\Driver;

/**
 * 
 * The Plugwise_Request object is responsible for building the request string
 * to the usb serial controller.
 * 
 * @package PlugWeb
 * @subpackage Driver
 * @copyright Copyright (C) 2010 Dirk Engels Websolutions. All rights reserved.
 * @author Dirk Engels (http://www.dirkengels.com)
 *
 */
class Request {
	const MAC_PREFIX = '000D6F0000';
	const ACTION_STICK_INIT = '000A';
	const ACTION_POWER_INFO = '0012';
	const ACTION_POWER_SWITCH = '0017';
	const ACTION_DEVICE_INFO = '0023'; 
	const ACTION_DEVICE_CALIBRATION = '0026';
	const ACTION_DEVICE_BUFFER = '0048';
	const ACTION_CLOCK_INFO = '003E';
	
	/**
	 * 
	 * Building the request string to initialize the stick
	 * @return string
	 */
	public function actionInitStick() {
		// 000A
		$str = self::ACTION_STICK_INIT;
		return $this->_renderActionString($str);
	}

	/**
	 * 
	 * Build the request string to switch a device on/off
	 * @param string $mac
	 * @param boolean $onOff
	 * @return string
	 */
	public function actionPowerSwitch($mac, $switchOnOff = false) {
		$onOff = ($switchOnOff) ? '01' : '00';

		// 0017 000D6F0000 729310 01
		$str = self::ACTION_POWER_SWITCH .
			self::MAC_PREFIX .$mac .
			$onOff;

		return $this->_renderActionString($str);
	}
	
	/**
	 * 
	 * Alias to switch a device on
	 * @param string $mac
	 * @return string
	 */
	public function actionPowerOn($mac) {
		return $this->actionPowerSwitch($mac, true);
	}

	/**
	 * 
	 * Alias to switch a device off
	 * @param string $mac
	 * @return string
	 */
	public function actionPowerOff($mac) {
		return $this->actionPowerSwitch($mac, false);
	}
	
	/**
	 * 
	 * Build the request string to get the current power usage of a device.
	 * @param string $mac
	 * @return string
	 */
	public function actionPowerInfo($mac) {
		// 0012 000D6F0000 729310
		$str = self::ACTION_POWER_INFO .
			self::MAC_PREFIX .
			$mac;

		return $this->_renderActionString($str);
	}

	/**
	 * 
	 * Build the request string to get the calibration of the device.
	 * @param string $mac
	 * @return string
	 */
	public function actionDeviceCalibration($mac) {
		// 0012 000D6F0000 729310
		$str = self::ACTION_DEVICE_CALIBRATION .
			self::MAC_PREFIX .
			$mac;

		return $this->_renderActionString($str);
	}

	/**
	 * 
	 * Build the request string to get the information of a device.
	 * @param string $mac
	 * @return string
	 */
	public function actionDeviceInfo($mac) {
		// 0012 000D6F0000 729310
		$str = self::ACTION_DEVICE_INFO .
			self::MAC_PREFIX .
			$mac;

		return $this->_renderActionString($str);
	}

	/**
	 * 
	 * Build the request string to get the buffer of the device.
	 * @param string $mac
	 * @return string
	 */
	public function actionDeviceBuffer($mac, $log) {
		// 0048 000D6F0000 729310 00044020
		$str = self::ACTION_DEVICE_BUFFER .
			self::MAC_PREFIX .
			$mac .
			$log;

		return $this->_renderActionString($str);
	}

	/**
	 * 
	 * Returns the clock information of the device. 
	 * @param string $mac
	 * @return string
	 */
	public function actionClockInfo($mac) {
		// 003E 000D6F0000 729310
		$str = self::ACTION_CLOCK_INFO . 
			self::MAC_PREFIX .
			$mac;

		return $this->_renderActionString($str);
	}
	
	/**
	 * 
	 * Assembles the request string with a header, footer and crc check.
	 * @param unknown_type $str
	 * @return string
	 */
	protected function _renderActionString($str) {
		$crc = $this->makeCrcCheckSum($str);
		$out = $this->_partHeader() .
			$str .
			$crc .
			$this->_partFooter();
		return $out;
	}

	/**
	 * 
	 * Request string header
	 * @return string
	 */
	protected function _partHeader() {
		return "\x05\x05\x03\x03";
	}
	
	/**
	 * 
	 * Request string footer
	 * @return string
	 */
	protected function _partFooter() {
		return "\x0d\x0a";
	}

    /**
     * 
     * The make Crc Checksum function creates a 16bit CRC checksum of the input
     * string.
     * @param string $string
     */
    public function makeCrcCheckSum($string) {
        $crc = 0x0000;
        for ($i = 0, $j = strlen($string); $i < $j; $i++) {
            $x = (($crc >> 8) ^ ord($string[$i])) & 0xFF;
            $x ^= $x >> 4;
            $crc = (($crc << 8) ^ ($x << 12) ^ ($x << 5) ^ $x) & 0xFFFF;
        }

        return strtoupper(dechex($crc));
    }
}