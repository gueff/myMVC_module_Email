 
# Requirements

- Linux
- php >=7.4
- myMVC
  - myMVC 3.2.x: https://github.com/gueff/myMVC/tree/3.2.x
  - ZIP: https://github.com/gueff/myMVC/archive/refs/heads/3.2.x.zip
  - Doku: https://mymvc.ueffing.net/

---

## Config

~~~php
//-------------------------------------------------------------------------------------
// Module Email

$aConfig['MODULE']['Email'] = array(

    // Spooler Folder
    'sAbsolutePathToFolderSpooler' => $aConfig['MVC_MODULES_DIR'] . '/Email/etc/data/spooler/',

    // Attachment Folder
    'sAbsolutePathToFolderAttachment' => $aConfig['MVC_MODULES_DIR'] . '/Email/etc/data/attachment/',

    // Number of e-mails to be processed simultaneously
    'iAmountToSpool' => 50,

    // max. time span for new delivery attempts (from "retry")
    'iMaxSecondsOfRetry' => (60 * 60 * 24 * 1), // 24h

    // max. time AFTER RETRY (`iMaxSecondsOfRetry`)
    // files in spooler will finally be deleted
    // so in fact calculation is: (iMaxSecondsOfRetry + iMaxSecondsOfDeletion)
    'iMaxSecondsOfDeletionAfterRetry' => (60 * 60 * 12 * 1), // x after iMaxSecondsOfRetry

    // callback function
    'oCallback' => function($oEmail) {

        // E-Mail Versand via SMTP
        return \Email\Model\Smtp::sendViaPhpMailer($oEmail);

        $oResponse = \MVC\DataType\DTArrayObject::create()
            ->add_aKeyValue(\MVC\DataType\DTKeyValue::create()->set_sKey('bSuccess')->set_sValue(true))
            ->add_aKeyValue(\MVC\DataType\DTKeyValue::create()->set_sKey('sMessage')->set_sValue("SUCCESS\t" . ' *** Closure *** '))
            ->add_aKeyValue(\MVC\DataType\DTKeyValue::create()->set_sKey('oException')->set_sValue(null));

        return $oResponse;
    },
    
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

---

## Usage

_init_  
~~~php
$oEmailController = new \Email\Controller\Index(
  Config::MODULE('Email')
);
~~~

_create and save Email_  
~~~php
// create email
$oEmail = Email::create()
    ->set_subject('Example Subject')
    ->set_recipientMailAdresses(array('bar@example.com',))
    ->set_senderMail('foo@example.com')
    ->set_senderName('foo')
    ->set_text("Foo\nbar\n")
    ->set_html('<h1>Foo</h1><p>bar</p>')
    ->set_oAttachment(DTArrayObject::create()
        // 1. attachment
        ->add_aKeyValue(DTKeyValue::create()->set_sKey('oEmailAttachment')->set_sValue(EmailAttachment::create()
            ->set_file('/tmp/foo.txt')
            ->set_name('foo.txt')       // <== optional; to overwrite original filename
        ))
    );
// save email
$oEmailController->oModelEmail->saveToSpooler(
    $oEmail
);
~~~

_Processes the mails to be sent in the spooler folder_ 
~~~php
$oEmailController->spool();
~~~

_Escalation to failed mails_  
~~~php
$oEmailController->escalate();
~~~

_Deletes older emails and attachments from spooler_    
~~~php
$oEmailController->cleanup();
~~~

---

## Explaining Spool

- E-mail files from the `retry` folder are read and moved to either `new` or `fail`, depending on the 
whether the maximum time for retry attempts ($iMaxSecondsOfRetry) for retry mails has been reached or not.
- There is still time for new delivery attempts_: E-Mail files are moved to the folder `new`.
- There is **no** time left for new delivery attempts_: Email files are moved to the `fail` folder
- E-mail files from the `new` folder are read and sent.
- _successful_: E-mail files are moved to the `done` folder.
- _failed_: Email files are moved to the `retry` folder

The maximum time period for new delivery attempts is defined in the config (see above) with the key `iMaxSecondsOfRetry`.

---

## Installation

~~~bash
./_install.sh
~~~

---

## Pro Tip for your main module

### adding routes and calling via cronjob

instead of running `spool` and `escalate` during runtime, consider calling them 
separately via cronjob.

_add routes to your main module_  
~~~php
// spool mails
\MVC\Route::GET(
    '/mail/spool/',
    'module=Email&c=Index&m=spool'
);
// escalate mails
\MVC\Route::GET(
    '/mail/escalate/',
    'module=Email&c=Index&m=escalate'
);
// cleanup spooler
\MVC\Route::GET(
    '/mail/cleanup/',
    'module=Email&c=Index&m=cleanup'
);
~~~

_then add cronjobs which call those routes_ 
~~~shell
# send emails
* * * * * cd /var/www/BLG/public; /usr/bin/php index.php "/mail/spool/" > /dev/null 2>/dev/null;
# escalate mails
* * * * * cd /var/www/BLG/public; /usr/bin/php index.php "/mail/escalate/" > /dev/null 2>/dev/null;
# cleanup spooler
* * * * * cd /var/www/BLG/public; /usr/bin/php index.php "/mail/cleanup/" > /dev/null 2>/dev/null;
~~~

### Logging

if you want to log operations in the module, you should add these event listeners to your module

~~~php
$aEvent = [
  
  // email module
  'email.model.index.saveToSpooler.done' => array(
      function(\MVC\DataType\DTArrayObject $oDTArrayObject) {
          $sFilename = $oDTArrayObject->getDTKeyValueByKey('sFilename')->get_sValue();
          $sData = $oDTArrayObject->getDTKeyValueByKey('sData')->get_sValue();
          $sSuccess = (true === $oDTArrayObject->getDTKeyValueByKey('bSuccess')->get_sValue()) ? 'true' : 'false';
          $sMessage = 'save: ' . $sData . ' => ' . $sFilename . ' (success: ' . $sSuccess . ')';
          \MVC\Log::write($sMessage, 'mail.log');
      }
  ),
  'email.model.index.spool' => array(
      function(\MVC\DataType\DTArrayObject $oDTArrayObject) {
          $oSendResponse = $oDTArrayObject->getDTKeyValueByKey('oSendResponse')->get_sValue();
          $oSpoolResponse = $oDTArrayObject->getDTKeyValueByKey('oSpoolResponse')->get_sValue();
            \MVC\Log::write(json_encode(\MVC\Convert::objectToArray($oSendResponse)), 'mail.log');
            \MVC\Log::write(json_encode(\MVC\Convert::objectToArray($oSpoolResponse)), 'mail.log');
      }
  ),
  'email.model.index._handleRetries' => array(
      function(\MVC\DataType\DTArrayObject $oDTArrayObject) {
          $sOldname = $oDTArrayObject->getDTKeyValueByKey('sOldname')->get_sValue();
          $sNewname = $oDTArrayObject->getDTKeyValueByKey('sNewname')->get_sValue();
          $sMoveSuccess = (true === $oDTArrayObject->getDTKeyValueByKey('bMoveSuccess')->get_sValue()) ? 'true' : 'false';
          $aMessage = $oDTArrayObject->getDTKeyValueByKey('aMessage')->get_sValue();
          \MVC\Log::write('email (retry: ' . $sMoveSuccess . '),  ' . $sOldname . ' => ' . $sMoveSuccess, 'mail.log');
  
          foreach ($aMessage as $sMessage)
          {
              \MVC\Log::write('email (retry: ' . $sMoveSuccess . '),  ' . $sMessage, 'mail.log');
          }
      }
  ),
  'email.model.index.escalate' => array(
      function(\MVC\DataType\DTArrayObject $oDTArrayObject) {
          $sMailFileName = $oDTArrayObject->getDTKeyValueByKey('sMailFileName')->get_sValue();
          $sEscalatedFileName = $oDTArrayObject->getDTKeyValueByKey('sEscalatedFileName')->get_sValue();
          $sMessage = 'escalate: ' . $sMailFileName . ' => ' . $sEscalatedFileName;
          \MVC\Log::write($sMessage, 'mail.log');
      }
  ),
  'email.model.index.deleteEmailFile' => array(
      function(\MVC\DataType\DTArrayObject $oDTArrayObject) {
          $sUnlink = (true === $oDTArrayObject->getDTKeyValueByKey('bUnlink')->get_sValue()) ? 'true' : 'false';
          $sAbsoluteFilePath = $oDTArrayObject->getDTKeyValueByKey('sFile')->get_sValue();
          $sMessage = 'email (del: ' . $sUnlink . '), ' . $sAbsoluteFilePath;
          \MVC\Log::write($sMessage, 'mail.log');
      }
  ),
  'email.model.index.deleteEmailAttachment' => array(
      function(\MVC\DataType\DTArrayObject $oDTArrayObject) {
          $sUnlink = (true === $oDTArrayObject->getDTKeyValueByKey('bUnlink')->get_sValue()) ? 'true' : 'false';
          $sAbsoluteFilePath = $oDTArrayObject->getDTKeyValueByKey('sFile')->get_sValue();
          $sMessage = 'attachment (del: ' . $sUnlink . '), ' . $sAbsoluteFilePath;
          \MVC\Log::write($sMessage, 'mail.log');
      }
  ),
];

#-------------------------------------------------------------
# process: bind the declared ones

if ('develop' === \MVC\Config::get_MVC_ENV())
{
    \MVC\Event::processBindConfigStack($aEvent);
}
~~~

---

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
\MVC\Event::RUN('email.model.index.escalate',
    DTArrayObject::create()
        ->add_aKeyValue(
            DTKeyValue::create()->set_sKey('sMailFileName')->set_sValue($sMailFileName)
        )
        ->add_aKeyValue(
            DTKeyValue::create()->set_sKey('sEscalatedFileName')->set_sValue($sEscalatedFileName)
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
