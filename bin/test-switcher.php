#!/usr/bin/env php
<?php

include('bootstrap.php');

$mac = '76CE03';
$mac = '729310';

$device = new \PlugWeb\Driver\Device($mac, '/dev/ttyUSB0');
#echo var_dump($device);
$device->initStick();
while (true) {
	$device->powerSwitch(true);
	sleep(3);
	$device->powerSwitch(false);
	sleep(3);
}

