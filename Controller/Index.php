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

class Index implements \MVC\MVCInterface\Controller
{
    /**
     * @var \Email\Model\Index
     */
    public $oModelEmail;

    public static function __preconstruct ()
    {
        ;
    }

    /**
     * @throws \ReflectionException
     */
	public function __construct(array $aConfig = array())
	{
        (true === empty($aConfig))
            ? $aConfig = \MVC\Config::MODULE('Email')
            : false
        ;
		$this->oModelEmail = new \Email\Model\Index(
		    Config::create($aConfig)
        );
	}

	/**
	 * Processes the mails to be sent in the spooler folder
	 */
	public function spool()
	{
		return $this->oModelEmail->spool();
	}
	
	/**
	 * Escalation to failed mails
	 */
	public function escalate()
	{
		return $this->oModelEmail->escalate();
	}

    /**
     * deletes older emails and attachments from spooler
     * @return null
     * @throws \ReflectionException
     */
    public function cleanup()
    {
        return $this->oModelEmail->cleanup();
    }

    public function __destruct()
    {
        ;
    }
}
