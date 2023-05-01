<?php
#---------------------------------------------------------------
require_once realpath(__DIR__ . '/../../../../../') . '/application/config/util/bootstrap.php';
\MVC\Config::init(get($GLOBALS['aConfig'], array()));
\MVC\Cache::init(\MVC\Config::get_MVC_CACHE_CONFIG());
\MVC\Cache::autoDeleteCache('DataType', 0);

#---------------------------------------------------------------
#  Defining DataType Classes

// Classes created by this script are placed into folder:
// `/modules/Blg/DataType/`
// @see https://mymvc.ueffing.net/3.1.x/generating-datatype-classes

$sCurrentModuleName = basename(realpath(__DIR__ . '/../../../'));
$sDataTypeDir = realpath(__DIR__ . '/../../../') . '/DataType/';

// base setup
$aDataType = array(
    'dir' => $sDataTypeDir,
    'unlinkDir' => false
);

// classes
$aDataType['class'][] = array(
    'name' => 'Config',
    'file' => 'Config.php',
    'namespace' => $sCurrentModuleName . '\\DataType',
    'createHelperMethods' => true,
    'constant' => array(
    ),
    'property' => array(
        array('key' => 'sAbsolutePathToFolderSpooler', 'var' => 'string',),
        array('key' => 'sAbsolutePathToFolderAttachment', 'var' => 'string',),
        array('key' => 'aIgnoreFile', 'var' => 'array', 'value' => array('..', '.', '.ignoreMe')),
        array('key' => 'sFolderNew', 'var' => 'string', 'value' => 'new'),
        array('key' => 'sFolderDone', 'var' => 'string', 'value' => 'done'),
        array('key' => 'sFolderRetry', 'var' => 'string', 'value' => 'retry'),
        array('key' => 'sFolderFail', 'var' => 'string', 'value' => 'fail'),
        array('key' => 'iAmountToSpool', 'value' => 10, 'var' => 'int'),
        array('key' => 'iMaxSecondsOfRetry', 'value' => (60 * 60 * 2), 'var' => 'int'),
        array('key' => 'oCallback', 'var' => '\Closure', 'value' => null),
    ),
);

// classes
$aDataType['class'][] = array(
    'name' => 'Email',
    'namespace' => $sCurrentModuleName . '\\DataType',
    'createHelperMethods' => true,
    'constant' => array(
    ),
    'property' => array(
        array('key' => 'subject', 'var' => 'string',),
        array('key' => 'recipientMailAdresses', 'var' => 'array',),
        array('key' => 'text', 'var' => 'string',),
        array('key' => 'html', 'var' => 'string',),
        array('key' => 'senderMail', 'var' => 'string',),
        array('key' => 'senderName', 'var' => 'string',),
        array(
            'key' => 'oAttachment',
            'var' => '\\MVC\\DataType\\DTArrayObject',
            'value' => 'null',
        ),
    ),
);

// classes
$aDataType['class'][] = array(
    'name' => 'EmailAttachment',
    'namespace' => $sCurrentModuleName . '\\DataType',
    'createHelperMethods' => true,
    'constant' => array(
    ),
    'property' => array(
        array('key' => 'name', 'var' => 'string',),
//                array('key' => 'content',),
        array('key' => 'file', 'var' => 'string',),
    ),
);

#---------------------------------------------------------------
#  create!

\MVC\Generator\DataType::create(56)->initConfigArray($aDataType);