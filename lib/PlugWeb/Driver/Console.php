<?php

namespace PlugWeb\Driver;

/**
 * 
 * The Console object provides the functionalities to run the driver from the
 * command line. A small executable script is used to instantiate this class. 
 *  
 * @package PlugWeb
 * @subpackage Driver
 * @copyright Copyright (C) 2010 Dirk Engels Websolutions. All rights reserved.
 * @author Dirk Engels (http://www.dirkengels.com)
 *
 */
class Console {

    protected $_consoleOpts = NULL;

    /**
     * 
     * Daemon constructor method
     * @param \Zend_Console_Getopt $consoleOpts
     */
    public function __construct(\Zend_Console_Getopt $consoleOpts = NULL) {
        $this->setConsoleOpts($consoleOpts);
    }


    /**
     * 
     * Returns an object containing the console arguments.
     * @return Zend_Console_Getopt
     */
    public function getConsoleOpts() {
        // Initialize default console options
        if (is_NULL($this->_consoleOpts)) {
            $this->_consoleOpts = new \Zend_Console_Getopt(
                array(
                    'mac|m=s'         => 'Mac address of plugwise device',
                    'action|a=s'    => 'Action switch-on, switch-off, info, status',
                    'help|h'        => 'Show help message (this message)',
                )
            );
        }
        return $this->_consoleOpts;
    }


    /**
     * 
     * Sets new console arguments
     * @param Zend_Console_Getopt $consoleOpts
     * @return $this
     */
    public function setConsoleOpts(Zend_Console_Getopt $consoleOpts = NULL) {
        if ($consoleOpts === NULL) {
            $consoleOpts = $this->getConsoleOpts();
        }

        // Parse Options
        try {
            $consoleOpts->parse();
        } catch (Zend_Console_Getopt_Exception $e) {
            echo $e->getUsageMessage();
            exit;
        }
        $this->_consoleOpts = $consoleOpts;

        return $this;
    }


    /**
     * Main method to start the command line tool. 
     */
    public function run() {
        // Set action
        $mac = $this->_consoleOpts->getOption('mac');
        $action = $this->_consoleOpts->getOption('action');

        $device = new \PlugWeb\Driver\Device($mac);
        $data = array();
        switch($action) {
            case 'switch-on':
                $data =$device->powerSwitchOn();
                break;
            case 'switch-off':
                $data = $device->powerSwitchOff();
                break;
            case 'power':
                $data = $device->powerInfo();
                $this->_printData($data);
                break;
            case 'info':
                $data = $device->deviceInfo();
                $this->_printData($data);
                break;
            case 'status':
                $data = $device->powerInfo();
                $this->_printData($data);
                break;
            default:
                echo $this->_consoleOpts->getUsageMessage();
                exit;
        }
        exit;
    }


    /**
     * Prints an array to STDOUT (using echo)
     * @param array $data
     */
    protected function _printData($data) {
        echo "Data\n";
        echo "====\n";
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                echo ucfirst($key) . "\n";
                echo str_repeat('-', strlen($key)) . "\n";
                $this->_printDataArray($value);
                echo "\n";
            } else {
                echo " - " . $key . ":\t\t" . $value . "\n";
            }
        }
        echo "\n";
    }


}
