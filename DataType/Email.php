<?php

/**
 * @name $EmailDataType
 */
namespace Email\DataType;

use MVC\MVCTrait\TraitDataType;

class Email
{
	use TraitDataType;

	const DTHASH = '8fbd918f3b544b007e86ad79847bd520';

	/**
	 * @required false
	 * @var string
	 */
	protected $subject;

	/**
	 * @required false
	 * @var array
	 */
	protected $recipientMailAdresses;

	/**
	 * @required false
	 * @var string
	 */
	protected $text;

	/**
	 * @required false
	 * @var string
	 */
	protected $html;

	/**
	 * @required false
	 * @var string
	 */
	protected $senderMail;

	/**
	 * @required false
	 * @var string
	 */
	protected $senderName;

	/**
	 * @required false
	 * @var \MVC\DataType\DTArrayObject
	 */
	protected $oAttachment;

	/**
	 * Email constructor.
	 * @param array $aData
	 * @throws \ReflectionException 
	 */
	public function __construct(array $aData = array())
	{
		\MVC\Event::RUN ('Email.__construct.before', \MVC\DataType\DTArrayObject::create($aData)->add_aKeyValue(\MVC\DataType\DTKeyValue::create()->set_sKey('aBacktrace')->set_sValue(\MVC\Debug::prepareBacktraceArray(debug_backtrace()))));

		$this->subject = '';
		$this->recipientMailAdresses = array();
		$this->text = '';
		$this->html = '';
		$this->senderMail = '';
		$this->senderName = '';
		$this->oAttachment = null;

		foreach ($aData as $sKey => $mValue)
		{
			$sMethod = 'set_' . $sKey;

			if (method_exists($this, $sMethod))
			{
				$this->$sMethod($mValue);
			}
		}

		\MVC\Event::RUN ('Email.__construct.after', \MVC\DataType\DTArrayObject::create($aData));
	}

    /**
     * @param array $aData
     * @return Email
     * @throws \ReflectionException
     */
    public static function create(array $aData = array())
    {
        \MVC\Event::RUN ('Email.create.before', \MVC\DataType\DTArrayObject::create($aData)->add_aKeyValue(\MVC\DataType\DTKeyValue::create()->set_sKey('aBacktrace')->set_sValue(\MVC\Debug::prepareBacktraceArray(debug_backtrace()))));
        
        $oObject = new self($aData);

        \MVC\Event::RUN ('Email.create.after', \MVC\DataType\DTArrayObject::create()->add_aKeyValue(\MVC\DataType\DTKeyValue::create()->set_sKey('Email')->set_sValue($oObject)));
        
        return $oObject;
    }

	/**
	 * @param string $aValue 
	 * @return $this
	 * @throws \ReflectionException
	 */
	public function set_subject($aValue)
	{
		\MVC\Event::RUN ('Email.set_subject.before', \MVC\DataType\DTArrayObject::create(array('subject' => $aValue))->add_aKeyValue(\MVC\DataType\DTKeyValue::create()->set_sKey('aBacktrace')->set_sValue(\MVC\Debug::prepareBacktraceArray(debug_backtrace()))));

		$this->subject = $aValue;

		return $this;
	}

	/**
	 * @param array $aValue 
	 * @return $this
	 * @throws \ReflectionException
	 */
	public function set_recipientMailAdresses($aValue)
	{
		\MVC\Event::RUN ('Email.set_recipientMailAdresses.before', \MVC\DataType\DTArrayObject::create(array('recipientMailAdresses' => $aValue))->add_aKeyValue(\MVC\DataType\DTKeyValue::create()->set_sKey('aBacktrace')->set_sValue(\MVC\Debug::prepareBacktraceArray(debug_backtrace()))));

		$this->recipientMailAdresses = $aValue;

		return $this;
	}

	/**
	 * @param string $aValue 
	 * @return $this
	 * @throws \ReflectionException
	 */
	public function set_text($aValue)
	{
		\MVC\Event::RUN ('Email.set_text.before', \MVC\DataType\DTArrayObject::create(array('text' => $aValue))->add_aKeyValue(\MVC\DataType\DTKeyValue::create()->set_sKey('aBacktrace')->set_sValue(\MVC\Debug::prepareBacktraceArray(debug_backtrace()))));

		$this->text = $aValue;

		return $this;
	}

	/**
	 * @param string $aValue 
	 * @return $this
	 * @throws \ReflectionException
	 */
	public function set_html($aValue)
	{
		\MVC\Event::RUN ('Email.set_html.before', \MVC\DataType\DTArrayObject::create(array('html' => $aValue))->add_aKeyValue(\MVC\DataType\DTKeyValue::create()->set_sKey('aBacktrace')->set_sValue(\MVC\Debug::prepareBacktraceArray(debug_backtrace()))));

		$this->html = $aValue;

		return $this;
	}

	/**
	 * @param string $aValue 
	 * @return $this
	 * @throws \ReflectionException
	 */
	public function set_senderMail($aValue)
	{
		\MVC\Event::RUN ('Email.set_senderMail.before', \MVC\DataType\DTArrayObject::create(array('senderMail' => $aValue))->add_aKeyValue(\MVC\DataType\DTKeyValue::create()->set_sKey('aBacktrace')->set_sValue(\MVC\Debug::prepareBacktraceArray(debug_backtrace()))));

		$this->senderMail = $aValue;

		return $this;
	}

	/**
	 * @param string $aValue 
	 * @return $this
	 * @throws \ReflectionException
	 */
	public function set_senderName($aValue)
	{
		\MVC\Event::RUN ('Email.set_senderName.before', \MVC\DataType\DTArrayObject::create(array('senderName' => $aValue))->add_aKeyValue(\MVC\DataType\DTKeyValue::create()->set_sKey('aBacktrace')->set_sValue(\MVC\Debug::prepareBacktraceArray(debug_backtrace()))));

		$this->senderName = $aValue;

		return $this;
	}

	/**
	 * @param \MVC\DataType\DTArrayObject $aValue 
	 * @return $this
	 * @throws \ReflectionException
	 */
	public function set_oAttachment($aValue)
	{
		\MVC\Event::RUN ('Email.set_oAttachment.before', \MVC\DataType\DTArrayObject::create(array('oAttachment' => $aValue))->add_aKeyValue(\MVC\DataType\DTKeyValue::create()->set_sKey('aBacktrace')->set_sValue(\MVC\Debug::prepareBacktraceArray(debug_backtrace()))));

		$this->oAttachment = $aValue;

		return $this;
	}

	/**
	 * @return string
	 * @throws \ReflectionException
	 */
	public function get_subject()
	{
		\MVC\Event::RUN ('Email.get_subject.before', \MVC\DataType\DTArrayObject::create()->add_aKeyValue(\MVC\DataType\DTKeyValue::create()->set_sKey('subject')->set_sValue($this->subject))->add_aKeyValue(\MVC\DataType\DTKeyValue::create()->set_sKey('aBacktrace')->set_sValue(\MVC\Debug::prepareBacktraceArray(debug_backtrace()))));

		return $this->subject;
	}

	/**
	 * @return array
	 * @throws \ReflectionException
	 */
	public function get_recipientMailAdresses()
	{
		\MVC\Event::RUN ('Email.get_recipientMailAdresses.before', \MVC\DataType\DTArrayObject::create()->add_aKeyValue(\MVC\DataType\DTKeyValue::create()->set_sKey('recipientMailAdresses')->set_sValue($this->recipientMailAdresses))->add_aKeyValue(\MVC\DataType\DTKeyValue::create()->set_sKey('aBacktrace')->set_sValue(\MVC\Debug::prepareBacktraceArray(debug_backtrace()))));

		return $this->recipientMailAdresses;
	}

	/**
	 * @return string
	 * @throws \ReflectionException
	 */
	public function get_text()
	{
		\MVC\Event::RUN ('Email.get_text.before', \MVC\DataType\DTArrayObject::create()->add_aKeyValue(\MVC\DataType\DTKeyValue::create()->set_sKey('text')->set_sValue($this->text))->add_aKeyValue(\MVC\DataType\DTKeyValue::create()->set_sKey('aBacktrace')->set_sValue(\MVC\Debug::prepareBacktraceArray(debug_backtrace()))));

		return $this->text;
	}

	/**
	 * @return string
	 * @throws \ReflectionException
	 */
	public function get_html()
	{
		\MVC\Event::RUN ('Email.get_html.before', \MVC\DataType\DTArrayObject::create()->add_aKeyValue(\MVC\DataType\DTKeyValue::create()->set_sKey('html')->set_sValue($this->html))->add_aKeyValue(\MVC\DataType\DTKeyValue::create()->set_sKey('aBacktrace')->set_sValue(\MVC\Debug::prepareBacktraceArray(debug_backtrace()))));

		return $this->html;
	}

	/**
	 * @return string
	 * @throws \ReflectionException
	 */
	public function get_senderMail()
	{
		\MVC\Event::RUN ('Email.get_senderMail.before', \MVC\DataType\DTArrayObject::create()->add_aKeyValue(\MVC\DataType\DTKeyValue::create()->set_sKey('senderMail')->set_sValue($this->senderMail))->add_aKeyValue(\MVC\DataType\DTKeyValue::create()->set_sKey('aBacktrace')->set_sValue(\MVC\Debug::prepareBacktraceArray(debug_backtrace()))));

		return $this->senderMail;
	}

	/**
	 * @return string
	 * @throws \ReflectionException
	 */
	public function get_senderName()
	{
		\MVC\Event::RUN ('Email.get_senderName.before', \MVC\DataType\DTArrayObject::create()->add_aKeyValue(\MVC\DataType\DTKeyValue::create()->set_sKey('senderName')->set_sValue($this->senderName))->add_aKeyValue(\MVC\DataType\DTKeyValue::create()->set_sKey('aBacktrace')->set_sValue(\MVC\Debug::prepareBacktraceArray(debug_backtrace()))));

		return $this->senderName;
	}

	/**
	 * @return \MVC\DataType\DTArrayObject
	 * @throws \ReflectionException
	 */
	public function get_oAttachment()
	{
		\MVC\Event::RUN ('Email.get_oAttachment.before', \MVC\DataType\DTArrayObject::create()->add_aKeyValue(\MVC\DataType\DTKeyValue::create()->set_sKey('oAttachment')->set_sValue($this->oAttachment))->add_aKeyValue(\MVC\DataType\DTKeyValue::create()->set_sKey('aBacktrace')->set_sValue(\MVC\Debug::prepareBacktraceArray(debug_backtrace()))));

		return $this->oAttachment;
	}

	/**
	 * @return string
	 */
	public static function getPropertyName_subject()
	{
        return 'subject';
	}

	/**
	 * @return string
	 */
	public static function getPropertyName_recipientMailAdresses()
	{
        return 'recipientMailAdresses';
	}

	/**
	 * @return string
	 */
	public static function getPropertyName_text()
	{
        return 'text';
	}

	/**
	 * @return string
	 */
	public static function getPropertyName_html()
	{
        return 'html';
	}

	/**
	 * @return string
	 */
	public static function getPropertyName_senderMail()
	{
        return 'senderMail';
	}

	/**
	 * @return string
	 */
	public static function getPropertyName_senderName()
	{
        return 'senderName';
	}

	/**
	 * @return string
	 */
	public static function getPropertyName_oAttachment()
	{
        return 'oAttachment';
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
