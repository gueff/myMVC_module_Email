<?php
/**
 * Index.php
 *
 * @module Email
 * @package Email\Controller
 * @copyright ueffing.net
 * @author Guido K.B.W. Üffing <info@ueffing.net>
 * @license GNU GENERAL PUBLIC LICENSE Version 3. See application/doc/COPYING
 */

namespace Email\Controller;

use Email\DataType\Config;
use MVC\Log;
use MVC\Registry;

class Index
{
    /**
     * @var arry JSON
     */
    protected $aJson;

    /**
     * @var \Email\Model\Index
     */
    protected $oModelEmail;
	
    /**
     * Index constructor.
     * @param $sString
     * @throws \ReflectionException
     */
	public function __construct($sString)
	{
		Log::WRITE(__METHOD__);

        // decodiere JSON
        $this->aJson = json_decode($sString, true);
		
		$this->oModelEmail = new \Email\Model\Index(
		    Config::create(
		        Registry::get('MODULE_EMAIL_CONFIG')
            )
        );
	}	

	/**
	 * Arbeitet die zu versendenden mails im spooler ordner ab
	 */
	public function spool ()
	{
		Log::WRITE(__METHOD__);
		
		$this->oModelEmail->spool();
		exit();		
	}
	
	/**
	 * Eskalation zu gescheiterten Mails
	 */
	public function escalate ()
	{
		Log::WRITE(__METHOD__);
		
		$this->oModelEmail->escalate();
		exit();		
	}	
}
