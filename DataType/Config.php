<?php

/**
 * @name $EmailDataType
 */
namespace Email\DataType;

use MVC\MVCTrait\TraitDataType;

class Config
{
	use TraitDataType;

	const DTHASH = 'dedb04f415863929fd987f80fcbd6ebb';

	/**
	 * @required false
	 * @var string
	 */
	protected $sAbsolutePathToFolderSpooler;

	/**
	 * @required false
	 * @var string
	 */
	protected $sAbsolutePathToFolderAttachment;

	/**
	 * @required false
	 * @var array
	 */
	protected $aIgnoreFile;

	/**
	 * @required false
	 * @var string
	 */
	protected $sFolderNew;

	/**
	 * @required false
	 * @var string
	 */
	protected $sFolderDone;

	/**
	 * @required false
	 * @var string
	 */
	protected $sFolderRetry;

	/**
	 * @required false
	 * @var string
	 */
	protected $sFolderFail;

	/**
	 * @required false
	 * @var int
	 */
	protected $iAmountToSpool;

	/**
	 * @required false
	 * @var int
	 */
	protected $iMaxSecondsOfRetry;

	/**
	 * @required false
	 * @var \Closure
	 */
	protected $oCallback;

	/**
	 * Config constructor.
	 * @param array $aData
	 * @throws \ReflectionException 
	 */
	public function __construct(array $aData = array())
	{
		\MVC\Event::RUN ('Config.__construct.before', \MVC\DataType\DTArrayObject::create($aData)->add_aKeyValue(\MVC\DataType\DTKeyValue::create()->set_sKey('aBacktrace')->set_sValue(\MVC\Debug::prepareBacktraceArray(debug_backtrace()))));

		$this->sAbsolutePathToFolderSpooler = '';
		$this->sAbsolutePathToFolderAttachment = '';
		$this->aIgnoreFile = array(0=>'..',1=>'.',2=>'.ignoreMe',);
		$this->sFolderNew = "new";
		$this->sFolderDone = "done";
		$this->sFolderRetry = "retry";
		$this->sFolderFail = "fail";
		$this->iAmountToSpool = 10;
		$this->iMaxSecondsOfRetry = 7200;
		$this->oCallback = null;

		foreach ($aData as $sKey => $mValue)
		{
			$sMethod = 'set_' . $sKey;

			if (method_exists($this, $sMethod))
			{
				$this->$sMethod($mValue);
			}
		}

		\MVC\Event::RUN ('Config.__construct.after', \MVC\DataType\DTArrayObject::create($aData));
	}

    /**
     * @param array $aData
     * @return Config
     * @throws \ReflectionException
     */
    public static function create(array $aData = array())
    {
        \MVC\Event::RUN ('Config.create.before', \MVC\DataType\DTArrayObject::create($aData)->add_aKeyValue(\MVC\DataType\DTKeyValue::create()->set_sKey('aBacktrace')->set_sValue(\MVC\Debug::prepareBacktraceArray(debug_backtrace()))));
        
        $oObject = new self($aData);

        \MVC\Event::RUN ('Config.create.after', \MVC\DataType\DTArrayObject::create()->add_aKeyValue(\MVC\DataType\DTKeyValue::create()->set_sKey('Config')->set_sValue($oObject)));
        
        return $oObject;
    }

	/**
	 * @param string $aValue 
	 * @return $this
	 * @throws \ReflectionException
	 */
	public function set_sAbsolutePathToFolderSpooler($aValue)
	{
		\MVC\Event::RUN ('Config.set_sAbsolutePathToFolderSpooler.before', \MVC\DataType\DTArrayObject::create(array('sAbsolutePathToFolderSpooler' => $aValue))->add_aKeyValue(\MVC\DataType\DTKeyValue::create()->set_sKey('aBacktrace')->set_sValue(\MVC\Debug::prepareBacktraceArray(debug_backtrace()))));

		$this->sAbsolutePathToFolderSpooler = $aValue;

		return $this;
	}

	/**
	 * @param string $aValue 
	 * @return $this
	 * @throws \ReflectionException
	 */
	public function set_sAbsolutePathToFolderAttachment($aValue)
	{
		\MVC\Event::RUN ('Config.set_sAbsolutePathToFolderAttachment.before', \MVC\DataType\DTArrayObject::create(array('sAbsolutePathToFolderAttachment' => $aValue))->add_aKeyValue(\MVC\DataType\DTKeyValue::create()->set_sKey('aBacktrace')->set_sValue(\MVC\Debug::prepareBacktraceArray(debug_backtrace()))));

		$this->sAbsolutePathToFolderAttachment = $aValue;

		return $this;
	}

	/**
	 * @param array $aValue 
	 * @return $this
	 * @throws \ReflectionException
	 */
	public function set_aIgnoreFile($aValue)
	{
		\MVC\Event::RUN ('Config.set_aIgnoreFile.before', \MVC\DataType\DTArrayObject::create(array('aIgnoreFile' => $aValue))->add_aKeyValue(\MVC\DataType\DTKeyValue::create()->set_sKey('aBacktrace')->set_sValue(\MVC\Debug::prepareBacktraceArray(debug_backtrace()))));

		$this->aIgnoreFile = $aValue;

		return $this;
	}

	/**
	 * @param string $aValue 
	 * @return $this
	 * @throws \ReflectionException
	 */
	public function set_sFolderNew($aValue)
	{
		\MVC\Event::RUN ('Config.set_sFolderNew.before', \MVC\DataType\DTArrayObject::create(array('sFolderNew' => $aValue))->add_aKeyValue(\MVC\DataType\DTKeyValue::create()->set_sKey('aBacktrace')->set_sValue(\MVC\Debug::prepareBacktraceArray(debug_backtrace()))));

		$this->sFolderNew = $aValue;

		return $this;
	}

	/**
	 * @param string $aValue 
	 * @return $this
	 * @throws \ReflectionException
	 */
	public function set_sFolderDone($aValue)
	{
		\MVC\Event::RUN ('Config.set_sFolderDone.before', \MVC\DataType\DTArrayObject::create(array('sFolderDone' => $aValue))->add_aKeyValue(\MVC\DataType\DTKeyValue::create()->set_sKey('aBacktrace')->set_sValue(\MVC\Debug::prepareBacktraceArray(debug_backtrace()))));

		$this->sFolderDone = $aValue;

		return $this;
	}

	/**
	 * @param string $aValue 
	 * @return $this
	 * @throws \ReflectionException
	 */
	public function set_sFolderRetry($aValue)
	{
		\MVC\Event::RUN ('Config.set_sFolderRetry.before', \MVC\DataType\DTArrayObject::create(array('sFolderRetry' => $aValue))->add_aKeyValue(\MVC\DataType\DTKeyValue::create()->set_sKey('aBacktrace')->set_sValue(\MVC\Debug::prepareBacktraceArray(debug_backtrace()))));

		$this->sFolderRetry = $aValue;

		return $this;
	}

	/**
	 * @param string $aValue 
	 * @return $this
	 * @throws \ReflectionException
	 */
	public function set_sFolderFail($aValue)
	{
		\MVC\Event::RUN ('Config.set_sFolderFail.before', \MVC\DataType\DTArrayObject::create(array('sFolderFail' => $aValue))->add_aKeyValue(\MVC\DataType\DTKeyValue::create()->set_sKey('aBacktrace')->set_sValue(\MVC\Debug::prepareBacktraceArray(debug_backtrace()))));

		$this->sFolderFail = $aValue;

		return $this;
	}

	/**
	 * @param int $aValue 
	 * @return $this
	 * @throws \ReflectionException
	 */
	public function set_iAmountToSpool($aValue)
	{
		\MVC\Event::RUN ('Config.set_iAmountToSpool.before', \MVC\DataType\DTArrayObject::create(array('iAmountToSpool' => $aValue))->add_aKeyValue(\MVC\DataType\DTKeyValue::create()->set_sKey('aBacktrace')->set_sValue(\MVC\Debug::prepareBacktraceArray(debug_backtrace()))));

		$this->iAmountToSpool = $aValue;

		return $this;
	}

	/**
	 * @param int $aValue 
	 * @return $this
	 * @throws \ReflectionException
	 */
	public function set_iMaxSecondsOfRetry($aValue)
	{
		\MVC\Event::RUN ('Config.set_iMaxSecondsOfRetry.before', \MVC\DataType\DTArrayObject::create(array('iMaxSecondsOfRetry' => $aValue))->add_aKeyValue(\MVC\DataType\DTKeyValue::create()->set_sKey('aBacktrace')->set_sValue(\MVC\Debug::prepareBacktraceArray(debug_backtrace()))));

		$this->iMaxSecondsOfRetry = $aValue;

		return $this;
	}

	/**
	 * @param \Closure $aValue 
	 * @return $this
	 * @throws \ReflectionException
	 */
	public function set_oCallback($aValue)
	{
		\MVC\Event::RUN ('Config.set_oCallback.before', \MVC\DataType\DTArrayObject::create(array('oCallback' => $aValue))->add_aKeyValue(\MVC\DataType\DTKeyValue::create()->set_sKey('aBacktrace')->set_sValue(\MVC\Debug::prepareBacktraceArray(debug_backtrace()))));

		$this->oCallback = $aValue;

		return $this;
	}

	/**
	 * @return string
	 * @throws \ReflectionException
	 */
	public function get_sAbsolutePathToFolderSpooler()
	{
		\MVC\Event::RUN ('Config.get_sAbsolutePathToFolderSpooler.before', \MVC\DataType\DTArrayObject::create()->add_aKeyValue(\MVC\DataType\DTKeyValue::create()->set_sKey('sAbsolutePathToFolderSpooler')->set_sValue($this->sAbsolutePathToFolderSpooler))->add_aKeyValue(\MVC\DataType\DTKeyValue::create()->set_sKey('aBacktrace')->set_sValue(\MVC\Debug::prepareBacktraceArray(debug_backtrace()))));

		return $this->sAbsolutePathToFolderSpooler;
	}

	/**
	 * @return string
	 * @throws \ReflectionException
	 */
	public function get_sAbsolutePathToFolderAttachment()
	{
		\MVC\Event::RUN ('Config.get_sAbsolutePathToFolderAttachment.before', \MVC\DataType\DTArrayObject::create()->add_aKeyValue(\MVC\DataType\DTKeyValue::create()->set_sKey('sAbsolutePathToFolderAttachment')->set_sValue($this->sAbsolutePathToFolderAttachment))->add_aKeyValue(\MVC\DataType\DTKeyValue::create()->set_sKey('aBacktrace')->set_sValue(\MVC\Debug::prepareBacktraceArray(debug_backtrace()))));

		return $this->sAbsolutePathToFolderAttachment;
	}

	/**
	 * @return array
	 * @throws \ReflectionException
	 */
	public function get_aIgnoreFile()
	{
		\MVC\Event::RUN ('Config.get_aIgnoreFile.before', \MVC\DataType\DTArrayObject::create()->add_aKeyValue(\MVC\DataType\DTKeyValue::create()->set_sKey('aIgnoreFile')->set_sValue($this->aIgnoreFile))->add_aKeyValue(\MVC\DataType\DTKeyValue::create()->set_sKey('aBacktrace')->set_sValue(\MVC\Debug::prepareBacktraceArray(debug_backtrace()))));

		return $this->aIgnoreFile;
	}

	/**
	 * @return string
	 * @throws \ReflectionException
	 */
	public function get_sFolderNew()
	{
		\MVC\Event::RUN ('Config.get_sFolderNew.before', \MVC\DataType\DTArrayObject::create()->add_aKeyValue(\MVC\DataType\DTKeyValue::create()->set_sKey('sFolderNew')->set_sValue($this->sFolderNew))->add_aKeyValue(\MVC\DataType\DTKeyValue::create()->set_sKey('aBacktrace')->set_sValue(\MVC\Debug::prepareBacktraceArray(debug_backtrace()))));

		return $this->sFolderNew;
	}

	/**
	 * @return string
	 * @throws \ReflectionException
	 */
	public function get_sFolderDone()
	{
		\MVC\Event::RUN ('Config.get_sFolderDone.before', \MVC\DataType\DTArrayObject::create()->add_aKeyValue(\MVC\DataType\DTKeyValue::create()->set_sKey('sFolderDone')->set_sValue($this->sFolderDone))->add_aKeyValue(\MVC\DataType\DTKeyValue::create()->set_sKey('aBacktrace')->set_sValue(\MVC\Debug::prepareBacktraceArray(debug_backtrace()))));

		return $this->sFolderDone;
	}

	/**
	 * @return string
	 * @throws \ReflectionException
	 */
	public function get_sFolderRetry()
	{
		\MVC\Event::RUN ('Config.get_sFolderRetry.before', \MVC\DataType\DTArrayObject::create()->add_aKeyValue(\MVC\DataType\DTKeyValue::create()->set_sKey('sFolderRetry')->set_sValue($this->sFolderRetry))->add_aKeyValue(\MVC\DataType\DTKeyValue::create()->set_sKey('aBacktrace')->set_sValue(\MVC\Debug::prepareBacktraceArray(debug_backtrace()))));

		return $this->sFolderRetry;
	}

	/**
	 * @return string
	 * @throws \ReflectionException
	 */
	public function get_sFolderFail()
	{
		\MVC\Event::RUN ('Config.get_sFolderFail.before', \MVC\DataType\DTArrayObject::create()->add_aKeyValue(\MVC\DataType\DTKeyValue::create()->set_sKey('sFolderFail')->set_sValue($this->sFolderFail))->add_aKeyValue(\MVC\DataType\DTKeyValue::create()->set_sKey('aBacktrace')->set_sValue(\MVC\Debug::prepareBacktraceArray(debug_backtrace()))));

		return $this->sFolderFail;
	}

	/**
	 * @return int
	 * @throws \ReflectionException
	 */
	public function get_iAmountToSpool()
	{
		\MVC\Event::RUN ('Config.get_iAmountToSpool.before', \MVC\DataType\DTArrayObject::create()->add_aKeyValue(\MVC\DataType\DTKeyValue::create()->set_sKey('iAmountToSpool')->set_sValue($this->iAmountToSpool))->add_aKeyValue(\MVC\DataType\DTKeyValue::create()->set_sKey('aBacktrace')->set_sValue(\MVC\Debug::prepareBacktraceArray(debug_backtrace()))));

		return $this->iAmountToSpool;
	}

	/**
	 * @return int
	 * @throws \ReflectionException
	 */
	public function get_iMaxSecondsOfRetry()
	{
		\MVC\Event::RUN ('Config.get_iMaxSecondsOfRetry.before', \MVC\DataType\DTArrayObject::create()->add_aKeyValue(\MVC\DataType\DTKeyValue::create()->set_sKey('iMaxSecondsOfRetry')->set_sValue($this->iMaxSecondsOfRetry))->add_aKeyValue(\MVC\DataType\DTKeyValue::create()->set_sKey('aBacktrace')->set_sValue(\MVC\Debug::prepareBacktraceArray(debug_backtrace()))));

		return $this->iMaxSecondsOfRetry;
	}

	/**
	 * @return \Closure
	 * @throws \ReflectionException
	 */
	public function get_oCallback()
	{
		\MVC\Event::RUN ('Config.get_oCallback.before', \MVC\DataType\DTArrayObject::create()->add_aKeyValue(\MVC\DataType\DTKeyValue::create()->set_sKey('oCallback')->set_sValue($this->oCallback))->add_aKeyValue(\MVC\DataType\DTKeyValue::create()->set_sKey('aBacktrace')->set_sValue(\MVC\Debug::prepareBacktraceArray(debug_backtrace()))));

		return $this->oCallback;
	}

	/**
	 * @return string
	 */
	public static function getPropertyName_sAbsolutePathToFolderSpooler()
	{
        return 'sAbsolutePathToFolderSpooler';
	}

	/**
	 * @return string
	 */
	public static function getPropertyName_sAbsolutePathToFolderAttachment()
	{
        return 'sAbsolutePathToFolderAttachment';
	}

	/**
	 * @return string
	 */
	public static function getPropertyName_aIgnoreFile()
	{
        return 'aIgnoreFile';
	}

	/**
	 * @return string
	 */
	public static function getPropertyName_sFolderNew()
	{
        return 'sFolderNew';
	}

	/**
	 * @return string
	 */
	public static function getPropertyName_sFolderDone()
	{
        return 'sFolderDone';
	}

	/**
	 * @return string
	 */
	public static function getPropertyName_sFolderRetry()
	{
        return 'sFolderRetry';
	}

	/**
	 * @return string
	 */
	public static function getPropertyName_sFolderFail()
	{
        return 'sFolderFail';
	}

	/**
	 * @return string
	 */
	public static function getPropertyName_iAmountToSpool()
	{
        return 'iAmountToSpool';
	}

	/**
	 * @return string
	 */
	public static function getPropertyName_iMaxSecondsOfRetry()
	{
        return 'iMaxSecondsOfRetry';
	}

	/**
	 * @return string
	 */
	public static function getPropertyName_oCallback()
	{
        return 'oCallback';
	}

	/**
	 * @return false|string JSON
	 */
	public function __toString()
	{
        return $this->getPropertyJson();
	}

	/**
	 * @return false|string
	 */
	public function getPropertyJson()
	{
        return json_encode($this->getPropertyArray());
	}

	/**
	 * @return array
	 */
	public function getPropertyArray()
	{
        return get_object_vars($this);
	}

	/**
	 * @return array
	 * @throws \ReflectionException
	 */
	public function getConstantArray()
	{
		$oReflectionClass = new \ReflectionClass($this);
		$aConstant = $oReflectionClass->getConstants();

		return $aConstant;
	}

	/**
	 * @return $this
	 */
	public function flushProperties()
	{
		foreach ($this->getPropertyArray() as $sKey => $aValue)
		{
			$sMethod = 'set_' . $sKey;

			if (method_exists($this, $sMethod)) 
			{
				$this->$sMethod('');
			}
		}

		return $this;
	}

}
