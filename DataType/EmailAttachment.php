<?php

/**
 * @name $EmailDataType
 */
namespace Email\DataType;

use MVC\MVCTrait\TraitDataType;

class EmailAttachment
{
	use TraitDataType;

	const DTHASH = '6f482d927bafbf82e9c290657eb5c7df';

	/**
	 * @required false
	 * @var string
	 */
	protected $name;

	/**
	 * @required false
	 * @var string
	 */
	protected $file;

	/**
	 * EmailAttachment constructor.
	 * @param array $aData
	 * @throws \ReflectionException 
	 */
	public function __construct(array $aData = array())
	{
		\MVC\Event::RUN ('EmailAttachment.__construct.before', \MVC\DataType\DTArrayObject::create($aData)->add_aKeyValue(\MVC\DataType\DTKeyValue::create()->set_sKey('aBacktrace')->set_sValue(\MVC\Debug::prepareBacktraceArray(debug_backtrace()))));

		$this->name = '';
		$this->file = '';

		foreach ($aData as $sKey => $mValue)
		{
			$sMethod = 'set_' . $sKey;

			if (method_exists($this, $sMethod))
			{
				$this->$sMethod($mValue);
			}
		}

		\MVC\Event::RUN ('EmailAttachment.__construct.after', \MVC\DataType\DTArrayObject::create($aData));
	}

    /**
     * @param array $aData
     * @return EmailAttachment
     * @throws \ReflectionException
     */
    public static function create(array $aData = array())
    {
        \MVC\Event::RUN ('EmailAttachment.create.before', \MVC\DataType\DTArrayObject::create($aData)->add_aKeyValue(\MVC\DataType\DTKeyValue::create()->set_sKey('aBacktrace')->set_sValue(\MVC\Debug::prepareBacktraceArray(debug_backtrace()))));
        
        $oObject = new self($aData);

        \MVC\Event::RUN ('EmailAttachment.create.after', \MVC\DataType\DTArrayObject::create()->add_aKeyValue(\MVC\DataType\DTKeyValue::create()->set_sKey('EmailAttachment')->set_sValue($oObject)));
        
        return $oObject;
    }

	/**
	 * @param string $aValue 
	 * @return $this
	 * @throws \ReflectionException
	 */
	public function set_name($aValue)
	{
		\MVC\Event::RUN ('EmailAttachment.set_name.before', \MVC\DataType\DTArrayObject::create(array('name' => $aValue))->add_aKeyValue(\MVC\DataType\DTKeyValue::create()->set_sKey('aBacktrace')->set_sValue(\MVC\Debug::prepareBacktraceArray(debug_backtrace()))));

		$this->name = $aValue;

		return $this;
	}

	/**
	 * @param string $aValue 
	 * @return $this
	 * @throws \ReflectionException
	 */
	public function set_file($aValue)
	{
		\MVC\Event::RUN ('EmailAttachment.set_file.before', \MVC\DataType\DTArrayObject::create(array('file' => $aValue))->add_aKeyValue(\MVC\DataType\DTKeyValue::create()->set_sKey('aBacktrace')->set_sValue(\MVC\Debug::prepareBacktraceArray(debug_backtrace()))));

		$this->file = $aValue;

		return $this;
	}

	/**
	 * @return string
	 * @throws \ReflectionException
	 */
	public function get_name()
	{
		\MVC\Event::RUN ('EmailAttachment.get_name.before', \MVC\DataType\DTArrayObject::create()->add_aKeyValue(\MVC\DataType\DTKeyValue::create()->set_sKey('name')->set_sValue($this->name))->add_aKeyValue(\MVC\DataType\DTKeyValue::create()->set_sKey('aBacktrace')->set_sValue(\MVC\Debug::prepareBacktraceArray(debug_backtrace()))));

		return $this->name;
	}

	/**
	 * @return string
	 * @throws \ReflectionException
	 */
	public function get_file()
	{
		\MVC\Event::RUN ('EmailAttachment.get_file.before', \MVC\DataType\DTArrayObject::create()->add_aKeyValue(\MVC\DataType\DTKeyValue::create()->set_sKey('file')->set_sValue($this->file))->add_aKeyValue(\MVC\DataType\DTKeyValue::create()->set_sKey('aBacktrace')->set_sValue(\MVC\Debug::prepareBacktraceArray(debug_backtrace()))));

		return $this->file;
	}

	/**
	 * @return string
	 */
	public static function getPropertyName_name()
	{
        return 'name';
	}

	/**
	 * @return string
	 */
	public static function getPropertyName_file()
	{
        return 'file';
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
