 
# Requirements

- Linux
- php 7
- [myMVC > 1.1.1 (dev)](https://github.com/gueff/myMVC/tree/9d2fab5b4e7f9fcd57a788ab86a145c169e4c9ad)
    - ZIP: https://github.com/gueff/myMVC/archive/9d2fab5b4e7f9fcd57a788ab86a145c169e4c9ad.zip
        
## Config

~~~php
$aConfig['MODULE_EMAIL_CONFIG'] = array(

    // Spooler Folder
    'sAbsolutePathToFolderSpooler' => $aConfig['MVC_MODULES'] . '/Email/etc/data/spooler/',

    // Attachment Folder
    'sAbsolutePathToFolderAttachment' => $aConfig['MVC_MODULES'] . '/Email/etc/data/attachment/',

    // Number of e-mails to be processed simultaneously
    'iAmountToSpool' => 50,

    // max. time span for new delivery attempts (from "retry")
    'iMaxSecondsOfRetry' => (60 * 60 * 24), // 24h

    /**
     * SMTP account settings
     */
    'sHost' => '',
    'iPort' => 465, # ssl=465 | tls=587
    'sSecure' => 'ssl', # ssl | tls
    'bAuth' => true,
    'sUsername' => '',
    'sPassword' => '',
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


## Installation

~~~bash
./install.sh
~~~


## Module Events

`email.model.index.saveToSpooler.done`
~~~
Event::RUN('email.model.index.saveToSpooler.done',
    DTArrayObject::create()
        ->add_aKeyValue(DTKeyValue::create()->set_sKey('sFilename')->set_sValue($sFilename))
        ->add_aKeyValue(DTKeyValue::create()->set_sKey('sData')->set_sValue($sData))
        ->add_aKeyValue(DTKeyValue::create()->set_sKey('bSuccess')->set_sValue($bSuccess))
);
~~~

`email.model.index.spool`
~~~
Event::RUN('email.model.index.spool',  
    DTArrayObject::create()
    ->add_aKeyValue(DTKeyValue::create()->set_sKey('oSendResponse')->set_sValue($oSendResponse)) // bSuccess, sMessage, oException
    ->add_aKeyValue(DTKeyValue::create()->set_sKey('oSpoolResponse')->set_sValue($oSpoolResponse))    
);
~~~

`email.model.index._handleRetries`
~~~
Event::RUN('email.model.index._handleRetries',
    DTArrayObject::create()
        ->add_aKeyValue(DTKeyValue::create()->set_sKey('sOldname')->set_sValue($sOldName))
        ->add_aKeyValue(DTKeyValue::create()->set_sKey('sNewname')->set_sValue($sNewName))
        ->add_aKeyValue(DTKeyValue::create()->set_sKey('bMoveSuccess')->set_sValue($bRename))
        ->add_aKeyValue(DTKeyValue::create()->set_sKey('aMessage')->set_sValue($aMsg))
);
~~~

`email.model.index.escalate`
~~~
\MVC\Event::RUN('email.model.escalate',
    DTArrayObject::create()
        ->add_aKeyValue(
            DTKeyValue::create()->set_sKey('aFailed')->set_sValue($aFailed)
        )
);
~~~

`email.model.index.deleteEmailAttachment`
~~~
Event::RUN(
    'email.model.index.deleteEmailAttachment',
    DTArrayObject::create()
        ->add_aKeyValue(
            DTKeyValue::create()
                ->set_sKey('bUnlink')
                ->set_sValue($bUnlink)
        )
        ->add_aKeyValue(
            DTKeyValue::create()
                ->set_sKey('sFile')
                ->set_sValue($sAbsoluteFilePath)
        )
);
~~~

`email.model.index.deleteEmailFile`
~~~
Event::RUN(
    'email.model.index.deleteEmailFile',
    DTArrayObject::create()
        ->add_aKeyValue(
            DTKeyValue::create()
                ->set_sKey('bUnlink')
                ->set_sValue($bUnlink)
        )
        ->add_aKeyValue(
            DTKeyValue::create()
                ->set_sKey('sFile')
                ->set_sValue($sAbsoluteFilePath)
        )
);
~~~
