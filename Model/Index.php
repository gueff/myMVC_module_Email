<?php
/**
 * Index.php
 *
 * @module Email
 * @package Email\Model
 * @copyright ueffing.net
 * @author Guido K.B.W. Üffing <info@ueffing.net>
 * @license GNU GENERAL PUBLIC LICENSE Version 3. See application/doc/COPYING
 */

namespace Email\Model;

use Email\DataType\Config;
use Email\DataType\Email;
use Email\DataType\EmailAttachment;
use MVC\Closure;
use MVC\Convert;
use MVC\DataType\DTArrayObject;
use MVC\DataType\DTKeyValue;
use MVC\Event;
use MVC\File;
use MVC\Log;


class Index
{
	/**
	 * Max. Time of renewed delivery attempts for retry mails before they are moved to /fail folder.
	 * @var integer
	 */
	protected $iMaxSecondsOfRetry = (60 * 60 * 2); // 2 h
	
	/**
	 *
	 * @var string
	 */
	protected $sSpoolerNewPath;
	
	/**
	 *
	 * @var string
	 */
	protected $sSpoolerDonePath;
	
	/**
	 *
	 * @var string
	 */
	protected $sSpoolerRetryPath;
	
	/**
	 *
	 * @var string
	 */
	protected $sSpoolerFailedPath;

	/**
	 * number of mails to be processed by the spooler
	 * 5	=== 5 / minute
	 *		=== 300 / hour
	 *		=== 1000 / ~3,5 hours
	 * 
	 * Default value: 10
	 * 
	 * @var integer
	 */
	protected $iAmountToSpool = 10;

    /**
     * @var null
     */
    protected $oCallback;

    /**
     * @var Config
     */
    protected $oConfig;

    /**
     * @param \Email\DataType\Config $oConfig
     * @throws \ReflectionException
     */
	public function __construct (Config $oConfig)
    {
        $this->oConfig = $oConfig;

        // fallback abs spooler dir
        if (empty($this->oConfig->get_sAbsolutePathToFolderSpooler()) || false === file_exists($this->oConfig->get_sAbsolutePathToFolderSpooler()))
        {
            $this->oConfig->set_sAbsolutePathToFolderSpooler(realpath(__DIR__ . '/../') . '/etc/data/spooler/');
        }

        // fallback abs attachment dir
        if (empty($this->oConfig->get_sAbsolutePathToFolderAttachment()) || false === file_exists($this->oConfig->get_sAbsolutePathToFolderAttachment()))
        {
            $this->oConfig->set_sAbsolutePathToFolderAttachment(realpath(__DIR__ . '/../') . '/etc/data/attachment/');
        }

        $this->sSpoolerNewPath = realpath($this->oConfig->get_sAbsolutePathToFolderSpooler() . $this->oConfig->get_sFolderNew()) . '/';
        $this->sSpoolerDonePath = realpath($this->oConfig->get_sAbsolutePathToFolderSpooler() . $this->oConfig->get_sFolderDone()) . '/';
        $this->sSpoolerRetryPath = realpath($this->oConfig->get_sAbsolutePathToFolderSpooler() . $this->oConfig->get_sFolderRetry()) . '/';
        $this->sSpoolerFailedPath = realpath($this->oConfig->get_sAbsolutePathToFolderSpooler() . $this->oConfig->get_sFolderFail()) . '/';

        // set fallback smtp
        if (null === $this->oConfig->get_oCallback() || true === empty($this->oConfig->get_oCallback()))
        {
            $this->oConfig->set_oCallback(function(Email $oEmail) {
                \Email\Model\Smtp::sendViaPhpMailer($oEmail);
            });
        }
    }

	/**
	 * sets number of max. mails to be processed within a spool
     * @param $iAmountToSpool
     * @return void
     * @throws \ReflectionException
     */
	public function setAmountToSpool($iAmountToSpool)
	{
	    $this->oConfig->set_iAmountToSpool((int) $iAmountToSpool);
	}

	/**
	 * returns the maximum number of mails to be processed within a spool
     * @return int
     * @throws \ReflectionException
     */
	public function getAmountToSpool()
	{
		return $this->oConfig->get_iAmountToSpool();
	}

	/**
	 * stores a mail in the spooler folder "new
     * @param Email|null $oEmail
     * @return string
     * @throws \ReflectionException
     */
	public function saveToSpooler (Email $oEmail = null)
	{
		if (is_null($oEmail))
		{
			return '';
		}

        $oEmail = $this->saveAttachementsOfEmail($oEmail);
		$sFilename = $this->sSpoolerNewPath . uniqid () . '_' . date('Y-m-d_H-i-s') . '.json';
		$sData = json_encode(Convert::objectToArray($oEmail));
        $bSuccess = (true === file_put_contents($sFilename, $sData)) ? true : false;

        Event::run('email.model.index.saveToSpooler.done',
            DTArrayObject::create()
                ->add_aKeyValue(DTKeyValue::create()->set_sKey('sFilename')->set_sValue($sFilename))
                ->add_aKeyValue(DTKeyValue::create()->set_sKey('sData')->set_sValue($sData))
                ->add_aKeyValue(DTKeyValue::create()->set_sKey('bSuccess')->set_sValue($bSuccess))
        );

        if (false === file_put_contents($sFilename, $sData))
        {
            return '';
        }

		return $sFilename;
	}

	/**
	 * processes the mails to be sent in the spooler folder
     * @return array
     * @throws \ReflectionException
     */
	public function spool ()
	{
		$this->_handleRetries();

		// mails to be sent from New
		$aFiles = array_diff(scandir ($this->sSpoolerNewPath), $this->oConfig->get_aIgnoreFile());

		$iCnt = 0;
		$aResponse = array();

		foreach ($aFiles as $sFile)
		{
			$iCnt++;

			// limit of mails to be processed reached; abort.
			if ($iCnt > $this->oConfig->get_iAmountToSpool())
			{
				break;
			}

			// get Email
			$aMail = json_decode(file_get_contents($this->sSpoolerNewPath . $sFile), true);
			$oEmail = Email::create($aMail);

            // send eMail
            /** @var DTArrayObject $oSendResponse */
            $oSendResponse = $this->send($oEmail);
            $sOldName = $this->sSpoolerNewPath . $sFile;

            if (true === $oSendResponse->getDTKeyValueByKey('bSuccess')->get_sValue())
            {
                $sNewName = $this->sSpoolerDonePath . $sFile;
                $sStatus = basename($this->sSpoolerDonePath);
                $sMessage = 'move mail to "' . $sStatus . '"';
            }
            else
            {
                $sNewName = $this->sSpoolerRetryPath . $sFile;
                $sStatus = basename($this->sSpoolerRetryPath);
                $sMessage = 'move mail to "' . $sStatus . '"';
            }

            Log::write($sMessage, 'mail.log');

            $bRename = rename(
                $sOldName,
                $sNewName
            );

            Log::write('$bRename: ' . (int) $bRename, 'mail.log');

            $oSpoolResponse = DTArrayObject::create()
                ->add_aKeyValue(DTKeyValue::create()->set_sKey('bSuccess')->set_sValue($bRename))
                ->add_aKeyValue(DTKeyValue::create()->set_sKey('sMessage')->set_sValue($sMessage))

                ->add_aKeyValue(DTKeyValue::create()->set_sKey('sOldname')->set_sValue($sOldName))
                ->add_aKeyValue(DTKeyValue::create()->set_sKey('sNewname')->set_sValue($sNewName))
                ->add_aKeyValue(DTKeyValue::create()->set_sKey('sStatus')->set_sValue($sStatus))
            ;

            $oResponse = DTArrayObject::create()
                ->add_aKeyValue(DTKeyValue::create()->set_sKey('oSendResponse')->set_sValue($oSendResponse)) // bSuccess, sMessage, oException
                ->add_aKeyValue(DTKeyValue::create()->set_sKey('oSpoolResponse')->set_sValue($oSpoolResponse))
                ;

            $aResponse[] = $oResponse;

            Event::run('email.model.index.spool', $oResponse);
		}

		return $aResponse;
	}
	
	/**
	 * Moves mails from retry folder either to /new or to /fail depending on whether the Max.
     * Time of new delivery attempts for retry mails is reached or not.
     */
	protected function _handleRetries()
	{
		// get Retry Mails
		$aRetry = array_diff(scandir ($this->sSpoolerRetryPath), $this->oConfig->get_aIgnoreFile());

		foreach ($aRetry as $sFile)
		{			
			// Determine the age of the file
			$sFilemtime = filemtime($this->sSpoolerRetryPath . $sFile);

			// Calculate time difference
			$iTimeDiff = (time() - $sFilemtime);

			// Try shipping again;
			// so move to /new folder
			if ($iTimeDiff < $this->oConfig->get_iMaxSecondsOfRetry())
			{
                $sOldName = $this->sSpoolerRetryPath . $sFile;
                $sNewName = $this->sSpoolerNewPath . $sFile;

                $aMsg = array();
                $aMsg[] = "MAIL\t" . $sOldName . "\t" . '$iTimeDiff: ' . $iTimeDiff . ' less than $iMaxRetryDurationTime: ' . $this->oConfig->get_iMaxSecondsOfRetry() . ' (seconds)';
                $aMsg[] = "MAIL\t" . 'try sending again.; move to folder "new": ' . $sNewName;

				$bRename = rename(
                    $sOldName,
                    $sNewName
				);
			}
			// Don't try again;
			// Move final to /fail folder
			else
			{
                $sOldName = $this->sSpoolerRetryPath . $sFile;
                $sNewName = $this->sSpoolerFailedPath . $sFile;

                $aMsg = array();
                $aMsg[] = "MAIL\t" . $sOldName . "\t" . '$iTimeDiff: ' . $iTimeDiff . ' not less than $iMaxRetryDurationTime: ' . $this->oConfig->get_iMaxSecondsOfRetry() . ' (seconds)';
                $aMsg[] = "MAIL\t" . 'do not try sending again.; move to folder "fail": ' . $sNewName;

                $bRename = rename(
                    $sOldName,
                    $sNewName
				);
			}

            Event::run('email.model.index._handleRetries',
                DTArrayObject::create()
                    ->add_aKeyValue(DTKeyValue::create()->set_sKey('sOldname')->set_sValue($sOldName))
                    ->add_aKeyValue(DTKeyValue::create()->set_sKey('sNewname')->set_sValue($sNewName))
                    ->add_aKeyValue(DTKeyValue::create()->set_sKey('bMoveSuccess')->set_sValue($bRename))
                    ->add_aKeyValue(DTKeyValue::create()->set_sKey('aMessage')->set_sValue($aMsg))
            );
		}		
	}

    /**
     * @return array|false
     * @throws \ReflectionException
     */
	public function getEscalatedMails()
    {
        // Working folder is fail folder
        chdir($this->sSpoolerFailedPath);

        // First determine all fail mails
        $aAllFailed = array_diff(scandir ('./'), $this->oConfig->get_aIgnoreFile());

        // Now find all already escalated mails
        $aEscalated = glob('escalated*', GLOB_BRACE);

        // exclude escalated mails
        $aFailed = array_diff(
            $aAllFailed,
            $aEscalated
        );

        return $aFailed;
    }

	/**
	 * Escalates failed mails
	 * @throws \ReflectionException
	 */
	public function escalate()
	{
		$aFailed = $this->getEscalatedMails();

		foreach ($aFailed as $sFile)
		{
			$sMailFileName = $this->sSpoolerFailedPath . $sFile;
			$sEscalatedFileName = $this->sSpoolerFailedPath . 'escalated.' . $sFile;
			
			\MVC\Event::run('email.model.index.escalate',
			    DTArrayObject::create()
				->add_aKeyValue(
				    DTKeyValue::create()->set_sKey('sMailFileName')->set_sValue($sMailFileName)
				)
				->add_aKeyValue(
				    DTKeyValue::create()->set_sKey('sEscalatedFileName')->set_sValue($sEscalatedFileName)
				)					
			);
			
			$bRename = rename(
				$sMailFileName,
				$sEscalatedFileName
			);	
		}		
	}

    /**
     * @param \Email\DataType\Email $oEmail
     * @return array
     * @throws \ReflectionException
     */
    public static function getAttachmentArray(Email $oEmail)
    {
        $aAttachment = array();

        /** @var DTArrayObject $oDTArrayObject */
        foreach ($oEmail->get_oAttachment() as $aDTArrayObject)
        {
            /** @var DTKeyValue $aDTKeyValue */
            foreach ($aDTArrayObject as $aDTKeyValue)
            {
                $oEmailAttachment = EmailAttachment::create($aDTKeyValue['sValue']);

                $aAttachment[] = array(
                    'name' => $oEmailAttachment->get_name(),
                    'content' => file_get_contents($oEmailAttachment->get_file())
                );
            }
        }

        return $aAttachment;
    }

    /**
     * Send E-Mail
     * @param Email $oEmail
     * @return DTArrayObject
     * @throws \ReflectionException
     */
	public function send (Email $oEmail)
	{
	    // call Callback/Closure function
        $mResult = call_user_func(
            $this->oConfig->get_oCallback(),
            $oEmail
        );

        /** @var DTArrayObject $oDTArrayObject */
        $oDTArrayObject = (false === $mResult)
            ? \MVC\DataType\DTArrayObject::create()
                ->add_aKeyValue(\MVC\DataType\DTKeyValue::create()->set_sKey('bSuccess')->set_sValue(false))
                ->add_aKeyValue(\MVC\DataType\DTKeyValue::create()->set_sKey('sMessage')->set_sValue("ERROR\t" . ' *** Closure execution failed: ' . Closure::dump($this->oConfig->get_oCallback())))
                ->add_aKeyValue(\MVC\DataType\DTKeyValue::create()->set_sKey('oException')->set_sValue(null))
            : $mResult;

        // error occurred
        if (false === $oDTArrayObject->getDTKeyValueByKey('bSuccess')->get_sValue())
        {
            Event::run('mvc.error', $oDTArrayObject);
            Log::WRITE($oDTArrayObject->getDTKeyValueByKey('sMessage')->get_sValue(), 'mail.log');
            return $oDTArrayObject;
        }

//        Log::WRITE("SUCCESS\t" . ' *** Closure: ' . Closure::dump($this->oConfig->get_oCallback()), 'mail.log');
        return $oDTArrayObject;
	}

    /**
     * @param string $sAbsoluteFilePath
     * @return bool
     * @throws \ReflectionException
     */
	public function deleteAttachment($sAbsoluteFilePath = '')
    {
        $bUnlink = false;

        // security
        $sAbsoluteFilePath = $this->oConfig->get_sAbsolutePathToFolderAttachment() . File::secureFilePath(basename($sAbsoluteFilePath));

        if (true == file_exists($sAbsoluteFilePath))
        {
            $bUnlink = unlink($sAbsoluteFilePath);
        }

        Event::run(
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

        return $bUnlink;
    }

    /**
     * deletes email json-file in spooler folder
     * @param string $sAbsoluteFilePath
     * @return bool
     * @throws \ReflectionException
     */
	public function deleteEmailFile($sAbsoluteFilePath = '')
    {
        // security
        // Path must be to one of the set folders
        $sAbsoluteFilePath = File::secureFilePath($sAbsoluteFilePath);
        $bIsLocatedInAcceptedFolder = in_array(
            substr($sAbsoluteFilePath, 0, strlen($this->sSpoolerNewPath)),
            array(
                $this->sSpoolerNewPath,
                $this->sSpoolerDonePath,
                $this->sSpoolerFailedPath,
                $this->sSpoolerRetryPath
            )
        );

        $bUnlink = false;

        if (true === $bIsLocatedInAcceptedFolder || true == file_exists($sAbsoluteFilePath))
        {
            $bUnlink = unlink($sAbsoluteFilePath);
        }

        Event::run(
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

        return $bUnlink;
    }

    /**
     * moves an e-mail to folder /new
     * @param string $sCurrentStatusFolder
     * @param string $sBasenameFile
     * @return string $sNewName Abs.Filepath | empty=fail
     */
    public function renewEmail($sCurrentStatusFolder = '', $sBasenameFile = '')
    {
        $sPath = 'sSpooler' . ucfirst($sCurrentStatusFolder) . 'Path';

        $sOldName = $this->$sPath . $sBasenameFile;
        $sNewName = $this->sSpoolerNewPath . $sBasenameFile;
        $bRename = false;

        if (file_exists($sOldName) && $sOldName != $sNewName)
        {
            $bRename = rename(
                $sOldName,
                $sNewName
            );
        }

        if (true === $bRename)
        {
            return $sNewName;
        }

        return '';
    }

    /**
     * @param \Email\DataType\Email|null $oEmail
     * @return \Email\DataType\Email|null
     * @throws \ReflectionException
     */
    public function saveAttachementsOfEmail(Email $oEmail = null)
    {
        if (null === $oEmail || null === $oEmail->get_oAttachment())
        {
            return $oEmail;
        }

        /** @var DTKeyValue $oDTKeyValue */
        foreach ($oEmail->get_oAttachment()->get_aKeyValue() as $iKey => $oDTKeyValue)
        {
            /** @var EmailAttachment $oEmailAttachment */
            $oEmailAttachment = $oDTKeyValue->get_sValue();
            $oEmailAttachment = $this->saveAttachment($oEmailAttachment);
        }

        return $oEmail;
    }

    /**
     * @param \Email\DataType\EmailAttachment|null $oEmailAttachment
     * @return \Email\DataType\EmailAttachment|null
     * @throws \ReflectionException
     */
    public function saveAttachment(EmailAttachment $oEmailAttachment = null)
    {
        if (null === $oEmailAttachment)
        {
            return $oEmailAttachment;
        }

        $oFile = File::info($oEmailAttachment->get_file());
        $sName = (false === empty($oEmailAttachment->get_name()))
            ? $oEmailAttachment->get_name()
            : $oFile->get_basename()
        ;

        $sAbsPathFile = $this->oConfig->get_sAbsolutePathToFolderAttachment()
            . md5($oEmailAttachment) . '.'
            . uniqid(microtime(true), true) . '.'
            . $oFile->get_extension();

        // copy
        $bCopy = copy(
            $oEmailAttachment->get_file(),
            $sAbsPathFile
        );

        // set attachement of copied location on success
        if (true === $bCopy && file_exists($sAbsPathFile))
        {
            $oEmailAttachment
                ->set_file($sAbsPathFile)
                ->set_name($sName)
            ;
        }

        return $oEmailAttachment;
    }

    /**
     * @param int $iMaxSeconds | fallback: 172800 (2 * 24h)
     * @return void
     * @throws \ReflectionException
     */
    public function cleanup(int $iMaxSeconds = 0)
    {
        (0 === $iMaxSeconds)
            ? $iMaxSeconds = (
                abs(get(\MVC\Config::MODULE('Email')['iMaxSecondsOfRetry'], (60 * 60 * 24)))
                +
                abs(get(\MVC\Config::MODULE('Email')['iMaxSecondsOfDeletionAfterRetry'], (60 * 60 * 24)))
            )
            : false
        ;

        $sAbsolutePathToFolderAttachment = \MVC\Config::MODULE('Email')['sAbsolutePathToFolderAttachment'];
        $aFile = glob($sAbsolutePathToFolderAttachment . '*');
        $this->deleteFromFileArray($aFile, $iMaxSeconds, 'attachment');

        $sAbsolutePathToFolderSpooler = \MVC\Config::MODULE('Email')['sAbsolutePathToFolderSpooler'];
        $aFile = glob($sAbsolutePathToFolderSpooler . '/*/*');
        $this->deleteFromFileArray($aFile, $iMaxSeconds, 'email');
    }

    /**
     * @param array  $aFile
     * @param        $iMaxSeconds
     * @param string $sType
     * @return void
     * @throws \ReflectionException
     */
    private function deleteFromFileArray(array $aFile = array(), $iMaxSeconds, string $sType = '')
    {
        if (false === in_array($sType, array('email','attachment')))
        {
            return false;
        }

        $iTimestamp = time();

        foreach ($aFile as $sFile)
        {
            $sFile = File::secureFilePath($sFile);

            if (is_file($sFile))
            {
                if ($iTimestamp - filemtime($sFile) >= $iMaxSeconds)
                {
                    if ('email' === $sType)
                    {
                        $this->deleteEmailFile($sFile);
                    }
                    if ('attachment' === $sType)
                    {
                        $this->deleteAttachment($sFile);
                    }
                }
            }
        }
    }
}