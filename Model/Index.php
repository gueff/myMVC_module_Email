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
use LCP\Model\LCPHelper;
use MVC\DataType\DTArrayObject;
use MVC\DataType\DTKeyValue;
use MVC\Event;
use MVC\Log;


/**
 * 
 * @author Guido Üffing <guido.ueffing@mediafinanz.de>
 */
class Index
{
	/**
	 * Max. Zeit erneuter Zustellversuche für Retry-Mails
	 * bevor sie in /fail Ordner verschoben werden
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
	 * anzahl der abzuarbeitenden mails durch den spooler
	 * 5	=== 5 / minute
	 *		=== 300 / stunde
	 *		=== 1000 / ~3,5 stunden	
	 * 
	 * Default-Wert: 10
	 * 
	 * @var integer
	 */
	protected $iAmountToSpool = 10;

    /**
     * @var string
     */
	protected $sAbsolutePathToFolderSpooler = '';

    /**
     * @var string
     */
    protected $sAbsolutePathToFolderAttachment = '';

    /**
     * Index constructor.
     * @param Config $oConfig
     */
	public function __construct (Config $oConfig)
    {
        // fallback abs spooler dir
        if (empty($oConfig->get_sAbsolutePathToFolderSpooler()) || false === file_exists($oConfig->get_sAbsolutePathToFolderSpooler()))
        {
            $oConfig->set_sAbsolutePathToFolderSpooler(realpath(__DIR__ . '/../') . '/etc/data/spooler/');
        }

        // fallback abs attachment dir
        if (empty($oConfig->get_sAbsolutePathToFolderAttachment()) || false === file_exists($oConfig->get_sAbsolutePathToFolderAttachment()))
        {
            $oConfig->set_sAbsolutePathToFolderAttachment(realpath(__DIR__ . '/../') . '/etc/data/attachment/');
        }

        $this->sAbsolutePathToFolderSpooler = realpath($oConfig->get_sAbsolutePathToFolderSpooler());
        $this->sAbsolutePathToFolderAttachment = realpath($oConfig->get_sAbsolutePathToFolderAttachment());
        $this->sSpoolerNewPath = realpath($this->sAbsolutePathToFolderSpooler . '/' . $oConfig->get_sFolderNew()) . '/';
        $this->sSpoolerDonePath = realpath($this->sAbsolutePathToFolderSpooler . '/' . $oConfig->get_sFolderDone()) . '/';
        $this->sSpoolerRetryPath = realpath($this->sAbsolutePathToFolderSpooler . '/' . $oConfig->get_sFolderRetry()) . '/';
        $this->sSpoolerFailedPath = realpath($this->sAbsolutePathToFolderSpooler . '/' . $oConfig->get_sFolderFail()) . '/';
        $this->iAmountToSpool = $oConfig->get_iAmountToSpool();
        $this->iMaxSecondsOfRetry = $oConfig->get_iMaxSecondsOfRetry();
    }

	/**
	 * setzt Anzahl der max. abzuarbeitenden mails innerhalb eines spools
	 * 
	 * @param integer $iAmountToSpool
	 */
	public function setAmountToSpool($iAmountToSpool)
	{		
		$this->iAmountToSpool = (int) $iAmountToSpool;
	}
	
	/**
	 * liefert Anzahl der max. abzuarbeitenden mails innerhalb eines spools
	 * 
	 * @return integer
	 */
	public function getAmountToSpool()
	{
		return $this->iAmountToSpool;
	}

	/**
	 * speichert eine mail im spooler ordner "new"
	 * 
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
		
		$sFilename = $this->sSpoolerNewPath . uniqid () . '_' . date('Y-m-d_H-i-s');
		$sData = $oEmail->getPropertyJson();
        $bSuccess = (true === file_put_contents($sFilename, $sData)) ? true : false;

        Event::RUN('email.model.index.saveToSpooler.done',
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
	 * arbeitet die zu versendenden mails im spooler ordner ab
     *
     * @return array
     * @throws \ReflectionException
     */
	public function spool ()
	{
		$this->_handleRetries();
		
		// zu versendende mails aus New
		$aFiles = array_diff(scandir ($this->sSpoolerNewPath), array('..', '.'));

		$iCnt = 0;
		$aResponse = array();
		
		foreach ($aFiles as $sFile)			
		{
			$iCnt++;
			
			// limit an abzuarbeitenden mails erreicht; Abbruch.
			if ($iCnt > $this->iAmountToSpool)
			{
				break;
			}
			
			// Hole Email
			$aMail = json_decode(file_get_contents($this->sSpoolerNewPath . $sFile), true);
            $oEmail = Email::create($aMail);

            // eMail senden
            /** @var DTArrayObject $oSendResponse */
            $oSendResponse = $this->send($oEmail);
            $sMessage = '';
            $sOldName = $this->sSpoolerNewPath . $sFile;

            if (true === $oSendResponse->getDTKeyValueByKey('bSuccess')->get_sValue())
            {
                $sNewName = $this->sSpoolerDonePath . $sFile;
                $sStatus = basename($this->sSpoolerDonePath);
                $sMessage = 'verschiebe mail nach "' . $sStatus . '"';
            }
            else
            {
                $sNewName = $this->sSpoolerRetryPath . $sFile;
                $sStatus = basename($this->sSpoolerRetryPath);
                $sMessage = 'verschiebe mail nach "' . $sStatus . '"';
            }

            $bRename = rename(
                $sOldName,
                $sNewName
            );

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

            Event::RUN('email.model.index.spool', $oResponse);
		}

		return $aResponse;
	}
	
	/**
     * @todo Texte/Inhalte aus Config beziehen
     *
	 * Verschiebt Mails aus retry Ordner entweder nach /new oder nach /fail
	 * je nachdem ob die Max. Zeit erneuter Zustellversuche für Retry-Mails
	 * erreicht ist oder nicht. 
	 */
	protected function _handleRetries()
	{
		// get Retry Mails
		$aRetry = array_diff(scandir ($this->sSpoolerRetryPath), array('..', '.'));

		Log::WRITE($aRetry, 'debug.log');

		foreach ($aRetry as $sFile)
		{			
			// Alter der Datei feststellen 
			$sFilemtime = filemtime($this->sSpoolerRetryPath . $sFile);

			// Zeitdiff berechnen
			$iTimeDiff = (time() - $sFilemtime);
            Log::WRITE($iTimeDiff  . ' <  ' .  $this->iMaxSecondsOfRetry . ' ? ' . (int) ($iTimeDiff < $this->iMaxSecondsOfRetry), 'debug.log');

			// Erneuten Versand versuchen;
			// verschiebe daher zu /new Ordner
			if ($iTimeDiff < $this->iMaxSecondsOfRetry)
			{
                $sOldName = $this->sSpoolerRetryPath . $sFile;
                $sNewName = $this->sSpoolerNewPath . $sFile;

                $aMsg = array();
                $aMsg[] = "MAIL\t" . $sOldName . "\t" . '$iTimeDiff: ' . $iTimeDiff . ' kleiner als $iMaxRetryDurationTime: ' . $this->iMaxSecondsOfRetry . ' (Sekunden)';
                $aMsg[] = "MAIL\t" . 'Erneuten Versand versuchen; Verschiebe zu "new" Ordner: ' . $sNewName;

				$bRename = rename(
                    $sOldName,
                    $sNewName
				);
			}
			// Nicht erneut versuchen;
			// Verschiebe final zu /fail Ordner
			else
			{
                $sOldName = $this->sSpoolerRetryPath . $sFile;
                $sNewName = $this->sSpoolerFailedPath . $sFile;

                $aMsg = array();
                $aMsg[] = "MAIL\t" . $sOldName . "\t" . '$iTimeDiff: ' . $iTimeDiff . ' nicht kleiner als $iMaxRetryDurationTime: ' . $this->iMaxSecondsOfRetry . ' (Sekunden)';
                $aMsg[] = "MAIL\t" . 'Versand nicht erneut versuchen; Verschiebe zu "fail" Ordner: ' . $sNewName;

                $bRename = rename(
                    $sOldName,
                    $sNewName
				);
			}

            Event::RUN('email.model.index._handleRetries',
                DTArrayObject::create()
                    ->add_aKeyValue(DTKeyValue::create()->set_sKey('sOldname')->set_sValue($sOldName))
                    ->add_aKeyValue(DTKeyValue::create()->set_sKey('sNewname')->set_sValue($sNewName))
                    ->add_aKeyValue(DTKeyValue::create()->set_sKey('bMoveSuccess')->set_sValue($bRename))
                    ->add_aKeyValue(DTKeyValue::create()->set_sKey('aMessage')->set_sValue($aMsg))
            );
		}		
	}
	
	/**
     * @todo Texte/Inhalte aus Config beziehen
     *
	 * Eskaliert Failed Mails:
	 * - versendet Mail
	 * - erstellt Ticket
	 */
	public function escalate()
	{
		Log::WRITE(__METHOD__);
		
		// Arbeitsordner ist fail ordner
		chdir($this->sSpoolerFailedPath);
		
		// Ermittle zunächst sämtliche fail Mails
		$aAll = array_diff(scandir ('./'), array('..', '.'));
		
		// Ermittle nun alle bereits eskalierten Mails
		$aEscalated = glob('escalated*', GLOB_BRACE);
		
		// schließe eskalierte mails aus
		$aFail = array_diff(
			$aAll,
			$aEscalated
		);

		foreach ($aFail as $sFile)
		{
			$sMailFileName = $this->sSpoolerFailedPath . $sFile;
			$sEscalatedFileName = $this->sSpoolerFailedPath . 'escalated.' . $sFile;
			
			$sContent = file_get_contents($sMailFileName);				
			$aMail = json_decode($sContent, true);			
			$sDump = print_r($aMail, true);
		
			$sSubject = 'E-Mail konnte nicht versandt werden (' . basename($sMailFileName . ')');
			$sMessage = "Folgende Mail konnte *nicht* versandt werden:\n\n- File: " . $sMailFileName . "\n" 
				. "- Dump Inhalt:\n" . $sDump . "\n" 
				. str_repeat('-', 40) . "\n" 
				. "Das o.g. Mail-File wurde nach Versand dieser Eskalations-E-Mail umbenannt zu:\n" 
				. "- " . $sEscalatedFileName . "\n\n" 
				. 'Info: Weitere Benachrichtigungen (Eskalationsstufen) erfolgen nicht.';

			Log::WRITE("MAIL\tESKALATION\t" . $sSubject . "\n" . $sMessage);
			
			// Mail
			Log::WRITE("MAIL\tESKALATION\t" . 'Versende Mail...');
			
			$sUrl = 'https://live.mediafinanz.de/MVC/public/?module=default&c=receiver&m=index';

            /**
             * @todo Versende Info Mail an OTRS; erstelle Ticket
             */
			file_get_contents(
				$sUrl
				, false
				, stream_context_create(array(
				'http' => array(
					'header' => "Content-type: application/x-www-form-urlencoded\r\n"
					, 'method' => 'POST'
					, 'content' => http_build_query(array(
							'sKey' => 'GDPCg67vmW5SY3xCQoF9IPK2AkfYbuWKrg2kKpEgWCgGDPCg67vmW5SY3xCQoF9IPK2AkfYbuWKrg2kKpEgWCg'
						,	'sSubject' => $sSubject
						,	'sMessage' => $sMessage
						,	'sSenderEmail' => 'return@mediafinanz.de'
						,	'sRecipientEmail' => 'guido.ueffing@mediafinanz.de'
						,	'sRecipientName' => 'Guido Üffing'
					)),
				))));

			// mail-file umbenennen
			$bRename = rename(
				$sMailFileName,
				$sEscalatedFileName
			);			
			
			Log::WRITE("MAIL\tESKALATION\t" . 'Umbenennen von `' . $sMailFileName . '` =zu= `' . $sEscalatedFileName . '`');
		}		
	}

    /**
     * @todo bei "file_get_contents" einen Worker einsetzen (while(true) usw.)
     *
     * @param Email $oEmail
     * @return array
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
     */
	public function send (Email $oEmail)
	{
        $oDTArrayObject = Smtp::sendViaPhpMailer($oEmail);

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
        $sAbsoluteFilePath = $this->sAbsolutePathToFolderAttachment . '/' . LCPHelper::secureFilePath(basename($sAbsoluteFilePath));

        if (true == file_exists($sAbsoluteFilePath))
        {
            $bUnlink = unlink($sAbsoluteFilePath);
        }

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
        // Pfad muss zu einem der gesetzten Ordner sein
        $sAbsoluteFilePath = LCPHelper::secureFilePath($sAbsoluteFilePath);
        $bIsLocatedInAcceptedFolder = in_array(
            substr($sAbsoluteFilePath, 0, strlen($this->sSpoolerNewPath)),
            array(
                $this->sSpoolerNewPath,
                $this->sSpoolerDonePath,
                $this->sSpoolerFailedPath,
                $this->sSpoolerRetryPath
            )
        );

        Log::WRITE('$bIsLocatedInAcceptedFolder: ' . json_encode($bIsLocatedInAcceptedFolder), 'debug.log');
        $bUnlink = false;

        if (true === $bIsLocatedInAcceptedFolder || true == file_exists($sAbsoluteFilePath))
        {
            $bUnlink = unlink($sAbsoluteFilePath);
        }

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

        return $bUnlink;
    }

    /**
     * verschiebt eine E-Mail nach Ordner /new
     *
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

        Log::WRITE(json_encode($bRename) . "\t" . $sOldName . ' => ' . $sNewName , 'debug.log');

        if (true === $bRename)
        {
            return $sNewName;
        }

        return '';
    }

    /**
     * @param EmailAttachment $oEmailAttachment
     * @return string fail=leer
     */
    public function saveAttachment(EmailAttachment $oEmailAttachment)
    {
        $aInfo = pathinfo($oEmailAttachment->get_name());

        $sAbsPathFile = $this->sAbsolutePathToFolderAttachment . '/'
            . md5($oEmailAttachment) . '.'
            . uniqid(microtime(true), true) . '.'
            . $aInfo['extension'];

        $bSsave = (boolean) file_put_contents(
            $sAbsPathFile,
            base64_decode($oEmailAttachment->get_content())
        );

        if (true === $bSsave && file_exists($sAbsPathFile))
        {
            return $sAbsPathFile;
        }

        return '';
    }
}