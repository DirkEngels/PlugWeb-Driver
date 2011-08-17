<?php

namespace PlugWeb\Driver;

/**
 * 
 * The Plugwise_Device_Respons object reads the response from the serial driver
 * and split the different elements into an array. 
 *  
 * @package PlugWeb
 * @subpackage Driver
 * @copyright Copyright (C) 2010 Dirk Engels Websolutions. All rights reserved.
 * @author Dirk Engels (http://www.dirkengels.com)
 *
 */
class Response {

    /**
     * 
     * The read string function reads the first* 4 bytes, which contains the 
     * action code. The action code is used to switch to the corresponding 
     * action
     * @param string $string
     * @return array
     */
    public function readString($string) {
         $responseAction = substr($string, 4, 4);
        $string = substr($string, 8);
        switch($responseAction) {
            case '0000':
                return $this->_processAcknowledge($string);
            case '0011':
                return $this->_processInitStick($string);
            case '0013':
                return $this->_processPowerInfo($string);
            case '0024':
                return $this->_processDeviceInfo($string);
            case '0027':
                return $this->_processDeviceCalibration($string);
            case '0049':
                return $this->_processDeviceBuffer($string);
            case '003F':
                return $this->_processClockInfo($string);
            default:
                return array();
                break;
        }
        return array('error' => 'Command unknown');
    }


    /**
     * 
     * Read (code: 0000) Acknowledge
     * @param string $string
     * @return array
     */
    protected function _processAcknowledge($string) {
        if (strlen($string)!=12) {
            return array('error' => 'Wrong length of Response::Acknowlegde string');
        }

        // Prepare output data
        $data = array(
            'sequenceNumber' => hexdec(substr($string, 0, 4)),
            'responseCode' => substr($string, 4, 4),
            'crcString' => substr($string, 8, 4)
        );
        return $data;
    }


    /**
     * 
     * Read (code: 0011) Stick initialization
     * @param string $string
     * @return array
     */
    protected function _processInitStick($string) {
        if (strlen($string)!=50) {
            return array('error' => 'Wrong length of Response::InitStick string');
        }

        // Prepare output data
        $data = array(
            'sequenceNumber' => hexdec(substr($string, 0, 4)),
            'macAddress' => substr($string, 14, 6),
            'unknownParam' => hexdec(substr($string, 20, 2)),
            'networkOnline' => hexdec(substr($string, 22, 2)),
            'uniqueCode' => hexdec(substr($string, 24, 16)),
            'uniqueShort' => hexdec(substr($string, 40, 4)),
            'unusedParam' => hexdec(substr($string, 44, 2)),
            'crcString' => substr($string, 46, 4)
        );
        return $data;
    }


    /**
     * 
     * Read (code: 0013) Power Usage string
     * @param string $string
     * @return array
     */
    protected function _processPowerInfo($string) {
        if (strlen($string)!=52) {
            return array('error' => 'Wrong length of Response::PowerInfo string');
        }

        // Prepare output data
        $pulses1 = hexdec(substr($string, 20, 4));
        $pulses8 = hexdec(substr($string, 24, 4));
        $data = array(
            'sequenceNumber' => hexdec(substr($string, 0, 4)),
            'macAddress' => substr($string, 14, 6),
            'pulsesInterval1' => $pulses1,
            'pulsesInterval8' => hexdec(substr($string, 24, 4)),
            'pulsesTotal' => hexdec(substr($string, 28, 8)),
            'crcString' => substr($string, 48, 4)
        );
        return $data;
    }


    /**
     * 
     * Read (code: 0027) Calibration
     * @param string $string
     * @return array
     */
    protected function _processDeviceCalibration($string) {
//        if (strlen($string)!=56) {
//            return array('error' => 'Wrong length of Response::Calibration string');
//        }

        // Prepare output data
        $data = array(
            'sequenceNumber' => hexdec(substr($string, 0, 4)),
            'macAddress' => substr($string, 14, 6),
            'gainA' => hexdec(substr($string, 20, 8)),
            'gainB' => hexdec(substr($string, 28, 8)),
            'offTot' => hexdec(substr($string, 36, 8)),
            'offRuis' => hexdec(substr($string, 44, 8)),
            'crcString' => substr($string, 52, 4)
        );
        return $data;
    }


    /**
     * 
     * Read (code: 0049) Buffer
     * @param string $string
     * @return array
     */
    protected function _processDeviceBuffer($string) {
        // Prepare output data
        $data = array(
            'sequenceNumber' => hexdec(substr($string, 0, 4)),
            'macAddress' => substr($string, 14, 6),
            'logAddress' => hexdec(substr($string, 80, 8)),
            'logDate1' => hexdec(substr($string, 20, 8)),
            'logDate2' => hexdec(substr($string, 36, 8)),
            'logDate3' => hexdec(substr($string, 50, 8)),
            'logDate4' => hexdec(substr($string, 66, 8)),
            'logValue1' => hexdec(substr($string, 28, 8)),
            'logValue2' => hexdec(substr($string, 42, 8)),
            'logValue3' => hexdec(substr($string, 58, 8)),
            'logValue4' => hexdec(substr($string, 72, 8)),
            'crcString' => substr($string, 88, 4)
        );
        return $data;
    }


    /**
     * 
     * Read (code: 0013) Device Info string
     * @param string $string
     * @return array
     */
    protected function _processDeviceInfo($string) {
        if (strlen($string)!=66) {
            return array('error' => 'Wrong length of Response::DeviceInfo string');
        }

        $currentFrequency = (substr($string, 38, 2) == '85') ? 50 : 60;
        $logAddress = (hexdec(substr($string, 28, 8)) - 278528) / 32;
        $minutes = hexdec(substr($string, 24, 4));
        $clockDay = floor($minutes / 1440);
        $clockHour = floor( ($minutes - ($clockDay * 1440)) / 60);
        $clockMinute = ($minutes - (($clockDay * 1440) + ($clockHour * 60)));

        // Prepare output data
        $data = array(
            'sequenceNumber' => hexdec(substr($string, 0, 4)),
            'macAddress' => substr($string, 14, 6),
            'clockYear' => hexdec(substr($string, 20, 2)),
            'clockMonth' => hexdec(substr($string, 22, 2)),
            'clockMinutes' => $minutes,
            'clockDay' => $clockDay,
            'clockHour' => $clockHour,
            'clockMinute' => $clockMinute,
            'logAddress' => $logAddress,
            'currentState' => substr($string, 36, 2),
            'currentFrequency' => $currentFrequency,
            'currentVersion' => substr($string, 40, 12),
            'currentFirmware' => substr($string, 52, 8),
            'unknownParam' => substr($string, 48, 2),
            'otherParam1' => hexdec(substr($string, 50, 12)),
            'crcString' => substr($string, 60, 4)
        );
        return $data;
    }


    protected function _processClockInfo($string) {
        if (strlen($string)!=38) {
            return array('error' => 'Wrong length of Response::ClockInfo string');
        }

        // Prepare output data
        $data = array(
            'sequenceNumber' => hexdec(substr($string, 0, 4)),
            'macAddress' => substr($string, 14, 6),
            'clockHour' => hexdec(substr($string, 20, 2)),
            'clockMinute' => hexdec(substr($string, 22, 2)),
            'clockSecond' => hexdec(substr($string, 24, 2)),
            'clockDayOfTheWeek' => hexdec(substr($string, 26, 2)),
            'unknownParam' => substr($string, 28, 2),
            'otherParam1' => hexdec(substr($string, 30, 4)),
            'crcString' => substr($string, 34, 4)
        );
        return $data;
    }

}