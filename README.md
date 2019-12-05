 
# Requirements

- Linux
- php 7
- [myMVC > 1.1.1 (dev)](https://github.com/gueff/myMVC/tree/9d2fab5b4e7f9fcd57a788ab86a145c169e4c9ad)
    - ZIP: https://github.com/gueff/myMVC/archive/9d2fab5b4e7f9fcd57a788ab86a145c169e4c9ad.zip
        
## Config
~~~php
$aConfig['MODULE_EMAIL_CONFIG'] = array(

    // Spooler Ordner
    'sAbsolutePathToFolderSpooler' => '/var/www/LCP/modules/Email/spooler/',

    // Number of e-mails to be processed simultaneously
    'iAmountToSpool' => 10,

    // max. time span for new delivery attempts
    'iMaxSecondsOfRetry' => (60 * 60 * 2), // 2h
);
~~~

## Spool

- E-mail files from the `retry` folder are read and moved to either `new` or `fail`, depending on the 
whether the maximum time for retry attempts ($iMaxSecondsOfRetry) for retry mails has been reached or not.
	 
    - There is still time for new delivery attempts_: E-Mail files are moved to the folder `new`.
    - There is **no** time left for new delivery attempts_: Email files are moved to the `fail` folder
- E-mail files from the `new` folder are read and sent.
    - _successful_: E-mail files are moved to the `done` folder.
    - _failed_: Email files are moved to the `retry` folder


The maximum time period for new delivery attempts is defined in the config (see above) with the key `iMaxSecondsOfRetry`.


## Un/Installation

~~~bash
./install.sh
./uninstall.sh
~~~


## DataTypes

_Config_
~~~
config/DataType/*.php
~~~

_Generierung_
~~~php
php generateDataTypes.php
~~~


## Module Events


_\LCP\DataType\KeyValue_
~~~
Event::RUN(
    'email.model.index.send.response',
    \LCP\DataType\KeyValue::create()
        ->set_sType(gettype($sResponse))
        ->set_sValue($sResponse)
        ->set_sKey('$sResponse')
);
~~~
~~~
Event::RUN('email.model.index.saveToSpooler.done',
    \LCP\DataType\KeyValue::create()
        ->set_sType(gettype(false))
        ->set_sValue(false)
        ->set_sKey('$bSuccess')
);
~~~

_array_ 
~~~
Event::RUN('email.model.index._handleRetries', array(
    'sOldname' => $sOldName,
    'sNewname' => $sNewName,
    'bMoveSuccess' => $bRename,
    'aMessage' => $aMsg,
));
~~~
~~~
Event::RUN('email.model.index.spool', array(
    'sOldname' => $sOldName,
    'sNewname' => $sNewName,
    'bMoveSuccess' => $bRename,
    'aMessage' => $aMsg,
));
~~~