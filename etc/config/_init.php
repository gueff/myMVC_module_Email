<?php

// Modulename
$sAppModuleName = basename(realpath(__DIR__ . '/../../'));

// Config Path
// depending on env MVC_ENV
$sConfigFileName =
    __DIR__
    . '/'
    . $sAppModuleName
    . '/config/'
    . getenv('MVC_ENV')
    . '.php';

// load config
include $sConfigFileName;

// External composer Libraries
require_once __DIR__ . '/' . $sAppModuleName . '/vendor/autoload.php';