<?php

namespace PlugWeb\Driver;

/**
 * 
 * The Plugwise_Device object provides a set of device actions such as switching 
 * on/off devices and getting the current power usage.
 *  
 * @package PlugWeb
 * @subpackage Driver
 * @copyright Copyright (C) 2010 Dirk Engels Websolutions. All rights reserved.
 * @author Dirk Engels (http://www.dirkengels.com)
 *
 */
class Device {

    protected $_mac      = NULL;
    protected $_serial   = NULL;
    protected $_request  = NULL;
    protected $_response = NULL;

    public function __construct($mac) {
        $this->_mac = $mac;
    }


    /**
     * 
     * Gets the mac address
     * @return string
     */
    public function getMac() {
        return $this->_mac;
    }


    /**
     * 
     * Sets the mac addres
     * @param string $mac
     * @return \PlugWeb\Driver\Device
     */
    public function setMac($mac) {
        $this->_mac = $mac;
        return $this;
    }


    /**
     * 
     * Gets or initailizes the serial controller object
     * @return \PlugWeb\Driver\Serial
     */
    public function getSerial() {
        if ($this->_serial === NULL) {
            $this->_serial = new \PlugWeb\Driver\Serial('/dev/ttyUSB0');
         }
        return $this->_serial;
    }


    /**
     * 
     * Sets or initializes the serial controller object
     * @param Plugwise_Serial $serial
     * @return \PlugWeb\Driver\Device
     */
    public function setSerial($serial) {
        if (is_subclass_of($serial, '\PlugWeb\Driver\Serial')) {
            $this->_serial = $serial;
        }
        return $this;
    }


    /**
     * 
     * Gets or initailizes the serial controller request object
     * @return \PlugWeb\Driver\Request
     */
    public function getRequest() {
        if ($this->_request === NULL) {
            $this->_request = new \PlugWeb\Driver\Request();
         }
        return $this->_request;
    }


    /**
     * 
     * Sets or initializes the serial controller request object
     * @param \PlugWeb\Driver\Request $request
     * @return \PlugWeb\Driver\Device
     */
    public function setRequest($request) {
        if (is_subclass_of($request, '\PlugWeb\Driver\Request')) {
            $this->_request = $request;
        }
        return $this;
    }


    /**
     * 
     * Gets or initailizes the serial controller response object
     * @return \PlugWeb\Driver\Response
     */
    public function getResponse() {
        if ($this->_response === NULL) {
            $this->_response = new \PlugWeb\Driver\Response();
         }
        return $this->_response;
    }


    /**
     * 
     * Sets or initializes the serial controller response object
     * @param \PlugWeb\Driver\Response $response
     * @return \PlugWeb\Driver\Device
     */
    public function setResponse($response) {
        if (is_subclass_of($response, '\PlugWeb\Driver\Response')) {
            $this->_response = $response;
        }
        return $this;
    }


    /**
     * 
     * Sends a string to the serial device and optionally reads the output 
     * string and interpretes into an usable array.
     * @param string $input
     * @param bool $readString
     * @return array
     */
    public function sendString($input, $readString = FALSE) {
        $this->getSerial()->sendData($input);
        if ($readString) {
            $data = $this->getSerial()->readData();
            return $this->_formatData($data);
        }
        return array();
    }


    /**
     * 
     * Initializes the plugwise stick
     * @return array
     */
    public function initStick() {
        $input = $this->getRequest()->actionInitStick();        
        return $this->sendString($input, TRUE);
    }


    /**
     * 
     * Calibrates the plugwise circle
     * @return array
     */
    public function deviceCalibration() {
        $input = $this->getRequest()->actionDeviceCalibration($this->_mac);
        return $this->sendString($input, TRUE);
    }


    /**
     * 
     * Retreives the device info
     * @return array
     */
    public function deviceInfo() {
        $input = $this->getRequest()->actionDeviceInfo($this->_mac);
        return $this->sendString($input, TRUE);
    }


    /**
     * 
     * Reads the device buffer
     * @return array
     */
    public function deviceBuffer($logAddress = NULL) {
        // Get last log device if none given
        if ($logAddress === NULL) {
            $deviceInfo = $this->deviceInfo();
            if ((!isset($deviceInfo)) || (!isset($deviceInfo['data']))) {
                return array();
            }
            $logAddress = $deviceInfo['data']['logAddress'];
        }

        // Prepare input string
        $input = $this->getRequest()->actionDeviceBuffer(
            $this->_mac,
            $logAddress
        );

        return  $this->sendString($input, TRUE);
    }


    /**
     * 
     * Retreives the clock info
     * @return array
     */
    public function clockInfo() {
        $input = $this->getRequest()->actionClockInfo($this->_mac);
        return $this->sendString($input, TRUE);
    }


    /**
     * 
     * Switches the devices on
     */
    public function powerSwitch($switchOnOff = FALSE) {
        $input = $this->getRequest()->actionPowerSwitch($this->_mac, $switchOnOff);
        $this->getSerial()->sendData($input);
    }


    /**
     * 
     * Alias for enabling the power switch
     */
    public function powerSwitchOn() {
        return $this->powerSwitch(TRUE);
    }


    /**
     * 
     * Alias for disabling the power switch
     */
    public function powerSwitchOff() {
        return $this->powerSwitch(FALSE);
    }


    /**
     * 
     * Gets the current power usage
     * @return array
     */
    public function powerInfo() {
        $input = $this->getRequest()
            ->actionPowerInfo($this->_mac);
        $out = $this->sendString($input, TRUE);
        $calibration = $this->deviceCalibration();

        // Add KWH
        $out['calibration'] = $calibration['data'];
        $out['data']['kwh'] = $this->_pulsesToKwh(
            1,
            $calibration['data']['pulsesInterval1'],
            $calibration['data']['offRuis'],
            $calibration['data']['offTot'],
            $calibration['data']['gainA'],
            $calibration['data']['gainB']
        );

        return $out;
    }


    /**
     * 
     * Format data: take the lines starting the '00' and feed them to the
     * response object which transforms the string to a nice readable and 
     * formatted array. 
     * @param string $data
     * @return array
     */
    protected function _formatData($data) {
        $lines = explode("\r\n", $data);
        $out = array();
        foreach($lines as $line) {
            if (preg_match('/^00/', substr($line, 4, 4))) {
                if (count($out)==0) {
                    // First packet is acknowledge packet
                    $out['ack'] = $this->getResponse()->readString($line);
                } elseif (count($out)==1) {
                    // Second packet is the data packet
                    $out['data'] = $this->getResponse()->readString($line);
                } else {
                    // Are there any other packets?
                    $out['tmp'][] = $this->getResponse()->readString($line);
                }
            }
        }
        return $out;
    }


    private function _pulsesToKwh($seconds, $value, $offRuis, $offTot, $gainA, $gainB) {
        $pulses = $seconds * (((pow($value + $offRuis, 2.0) * $gainB) + (($value + $offRuis) * $gainA)) + $offTot);
        $result = ($pulses/ 1) / 468.9385193;
        return $result;
    }
    
}