<?php

namespace PlugWeb\Driver;

/**
 * 
 * The \Plugwise\Driver\Serial object is responsible for sending and receiving
 * data from and to the USB serial device (plugwise stick). It handles setting
 * up a connection with the device and setting its parameters such as baudrate.
 * 
 * @package PlugWeb
 * @subpackage Driver
 * @copyright Copyright (C) 2010 Dirk Engels Websolutions. All rights reserved.
 * @author Dirk Engels (http://www.dirkengels.com)
 *
 */
class Serial {

    protected $_device = NULL;
    protected $_handle = NULL;
    protected $_buffer = '';


    /**
     * 
     * The constructor only handles setting the device. 
     * 
     * @param \Plugwise\Driver\Device $device
     */
    public function __construct($device) {
        $this->setDevice($device);
    }


    /**
     * 
     * Closes the resource handle with the device.
     */
    public function __destruct() {
        if ($this->_handle !== NULL) {
            fclose($this->_handle);
        }
    }


    /**
     * 
     * Gets the \Plugwise\Driver\Device
     */
    public function getDevice() {
        return $this->_device;
    }


    /**
     * 
     * Sets the \Plugwise\Driver\Device
     * @param \Plugwise\Driver\Device $device
     */
    public function setDevice($device) {
        $this->_device = $device;
        $this->_init();

        return $this;
    }


    /**
     * 
     * Sends a command to the serial device
     * @param string $str
     */
    public function sendData($str) {
        try {
            $charsWritten = fwrite($this->_handle, $str);
        } catch (Exception $e) {
            echo $e->getMessage();
        }
        usleep( (int)(0.1 * 1000000) );
        return $charsWritten;
    }


    /**
     * 
     * Reads a number of characters of the serial device.
     * @param integer $count
     */
    public function readData($count = 0) {
        $content = '';
        while(strlen($content)<=$count) {
            $content .= fread($this->_handle, 8192);
            usleep( (int)(0.1 * 1000000) );
        }

        return $content;
    }


    /**
     * 
     * Opens the device handle and sets the necessary serial driver options.
     */
    protected function _init() {
        // Initialize baudrate
        $return = $this->_exec("stty -F " . $this->_device . " 15200", $returnData);

        $this->_handle = fopen($this->_device, "r+b");
        stream_set_blocking($this->_handle, 0);
    }


    /**
     * 
     * Executes a system command and get the default and error output.
     * @param string $cmd
     * @param string $out
     */
    protected function _exec($cmd, &$out = NULL)
    {
        $desc = array(
            1 => array("pipe", "w"),
            2 => array("pipe", "w")
        );

        $proc = proc_open($cmd, $desc, $pipes);

        $ret = stream_get_contents($pipes[1]);
        $err = stream_get_contents($pipes[2]);

        fclose($pipes[1]);
        fclose($pipes[2]);

        $retVal = proc_close($proc);

        if (func_num_args() == 2) {
            $out = array($ret, $err);
        }
        return $retVal;
    }

}