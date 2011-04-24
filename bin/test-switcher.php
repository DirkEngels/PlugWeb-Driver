#!/usr/bin/env php
<?php

include('bootstrap.php');

$mac = '76CE03';
$mac = '729310';

$device = new \PlugWeb\Driver\Device($mac);
$device->initStick();
$device->powerSwitch(true);
sleep(1);
$device->powerSwitch(false);