<?php


namespace Result;

use ArrayAccess;


/**
 * Class ResultException
 *
 * @package Ik\Exception
 */
class ResultException extends BaseException
    implements ArrayAccess, ExceptionInterface
{
    /******************************************************************************
     * RESULTS
     ******************************************************************************/

    const R_SUCCESS = 0;
    const R_FAILURE = 2;
    const E_INTERNAL = 4;
    const P_ALIAS = 'als';
    const P_MESSAGE = 'msg';
    const P_MESSAGE_ADMIN = 'msgAdm';
    const P_MESSAGE_ARG = 'msgArg';
    const P_STATUS = 'sts';
    const P_IGNORE_LOGGING = 'igl';
    /******************************************************************************
     * MAIN
     ******************************************************************************/

    const TRANSLATE_KEY_PREFIX = 'result-msg-';
    const TRANSLATE_KEY_PREFIX_ADMIN = 'result-msg-admin-';

    /******************************************************************************
     * STATUS
     ******************************************************************************/

    const STATUS_SUCCESS = 'success';
    const STATUS_FAILURE = 'failure';
    const STATUS_ERROR = 'error';

    static public $resultClassIndexMap;
    /**
     * @var array
     */
    static public $results
        = [
            self::R_SUCCESS  => array(
                self::P_MESSAGE => 'Success',
                self::P_STATUS  => self::STATUS_SUCCESS,
            ),
            self::R_FAILURE  => array(
                self::P_MESSAGE => 'Fail',
                self::P_STATUS  => self::STATUS_FAILURE,
            ),
            self::E_INTERNAL => array(
                self::P_MESSAGE => 'Internal error',
                self::P_STATUS  => self::STATUS_ERROR,
            ),
        ];
    /******************************************************************************
     * REFLECTION
     ******************************************************************************/

    static protected $_reflections;
    static protected $_reflectionConstants;
    static protected $_translatorFunction = '_t';

    protected $_messageAdmin;
    /**
     * Converts any exception to internal error ResultException
     *
     * @var int|string
     */
    protected $_globalCode;
    /******************************************************************************
     * EXCEPTION
     ******************************************************************************/

    protected $_innerException;
    /**
     * @var string
     */
    protected $_status = self::STATUS_FAILURE;
    /**
     * @var array
     */
    protected $_data = [];

    /**
     * @param string|int|\Exception $code
     * @param null                  $data
     * @param \Exception|null       $innerException
     *
     * @throws InvalidArgumentException
     * @throws OutOfRangeException
     */
    public function __construct(
        $code,
        $data = null,
        \Exception $innerException = null
    ) {
        // Init code
        if ($code === null) {
            throw new InvalidArgumentException("Empty result code arg");
        } elseif (is_int($code)) {
            $this->code = $code;
            if (isset(static::$results[$code])
                && self::R_SUCCESS !== $code
            ) {
                $this->_initData(static::$results);
            } elseif (isset(self::$results[$code])) {
                $this->_initData(self::$results);
            }
        } elseif ($code instanceof \Exception) {
            $this->_status = self::STATUS_ERROR;
            $this->code = self::E_INTERNAL;
            $this->message = $code->getMessage();
            $this->setData('innerCode', $code->getCode());
            $this->_globalCode = self::buildGlobalCode($this);
            return $this;
        }

        // Check code range & exists as const
        if ($code > 1000) {
            throw new OutOfRangeException("Result code [$code] out of range");
        } elseif ($this->getConstants($this->code) === false) {
            throw new InvalidArgumentException("Invalid result code [$code]");
        }

        // Build global code
        $this->_globalCode = self::buildGlobalCode($this);

        // Init message
        if (empty($this->message)) {
            $this->message = self::buildMessage($this);
        }

        // PreInit message arg
        if ($this->hasData(self::P_MESSAGE_ARG)) {
            $messageArg = $this->getData(self::P_MESSAGE_ARG);
        }

        // Init data
        if (is_array($data)) {
            $this->addData($data);
        } elseif (is_string($data)) {
            $messageArg = $data;
        } elseif ($data instanceof \Exception && $innerException === null) {
            $innerException = $data;
        }

        // Init message arg
        if (!empty($messageArg)) {
            $this->setMessageArg($messageArg);
        }

        // Init status
        if ($this->hasData(self::P_STATUS)) {
            $this->_status = $this->getData(self::P_STATUS);
        }

        // Unset system properties
        $this->unsetData(self::P_MESSAGE);
        $this->unsetData(self::P_STATUS);

        // Init Previous exception
        if ($innerException instanceof \Exception) {
            $this->setInnerException($innerException);
        }
    }

    /**
     * @param $results
     */
    protected function _initData(&$results)
    {
        if (is_array($results[$this->code])) {
            $this->addData($results[$this->code]);
        } elseif (is_string($results[$this->code])) {
            $this->message = $results[$this->code];
        }
    }

    /**
     * @param $data
     *
     * @return $this
     */
    public function addData($data)
    {
        $data = (array)$data;

        if (empty($data)) {
            return $this;
        }

        foreach ($data as $key => $value) {
            $this->setData($key, $value);
        }

        return $this;
    }

    /**
     * @param      $result
     * @param null $code
     *
     * @return int|string
     */
    public static function buildGlobalCode($result, $code = null)
    {
        if ($result instanceof self) {
            $resultClass = get_class($result);
            $resultCode = $result->getCode();
        } else {
            $resultClass = (string)$result;
            $resultCode = (int)$code;
        }

        if (self::R_SUCCESS === $resultCode) {
            return self::R_SUCCESS;
        }

        $resultClassIndex = self::getClassIndex($resultClass);

        $resultCode = ($resultClassIndex * 1000) + $resultCode;

        return $resultCode;
    }

    /** Returns class index by class name
     *
     * @param $class
     *
     * @throws RuntimeException
     * @return null|int
     */
    public static function getClassIndex($class)
    {
        if (isset(self::getClassIndexMap()[$class])) {
            return self::getClassIndexMap()[$class];
        } else {
            throw new RuntimeException("Undefined class index [$class]");
        }
    }

    /**
     * @param null $key
     *
     * @return mixed
     */
    public function getConstants($key = null)
    {
        $className = get_class($this);

        if (!isset(self::$_reflectionConstants[$className])) {
            self::$_reflectionConstants[$className] = self::getReflection($this)
                ->getConstants();
        }

        if ($key === null) {
            return self::$_reflectionConstants[$className];
        } else {
            return array_search($key, self::$_reflectionConstants[$className],
                true);
        }
    }

    /**
     * @param $object
     *
     * @return mixed
     */
    protected static function getReflection($object)
    {
        $reflectionClass = get_class($object);

        if (!isset(self::$_reflections[$reflectionClass])) {
            self::$_reflections[$reflectionClass]
                = new \ReflectionClass($object);
        }

        return self::$_reflections[$reflectionClass];
    }

    /**
     * @param ResultException $result
     *
     * @return string $message Result message
     */
    public static function buildMessage(ResultException $result)
    {
        // try to translate message
        $translateKey = self::TRANSLATE_KEY_PREFIX . $result->_globalCode;
        $message = self::translate($translateKey);

        return empty($message) ? $result->getAlias() : $message;
    }

    /**
     * @return string|null
     */
    public function getAlias()
    {
        if ($this->hasData(self::P_ALIAS)) {
            return $this->getData(self::P_ALIAS);
        } else {
            return $this->getConstants($this->code);
        }
    }

    /**
     * @param $key
     *
     * @return bool
     */
    public function hasData($key)
    {
        return isset($this->_data[$key]);
    }

    /**
     * @param null $key
     * @param null $default
     *
     * @return array|null
     */
    public function getData($key = null, $default = null)
    {
        if ($key === null) {
            return $this->_data;
        }

        return isset($this->_data[$key]) ? $this->_data[$key] : $default;
    }

    /**
     * @param $key
     * @param $value
     */
    public function setData($key, $value)
    {
        if ($key === self::P_MESSAGE_ARG) {
            return $this->setMessageArg($value);
        }

        if ($key === self::P_MESSAGE) {
            return $this->message = $value;
        }

        if ($key === self::P_MESSAGE_ADMIN) {
            return $this->_messageAdmin = $value;
        }

        $this->_data[$key] = $value;
    }

    public function setMessageArg($messageArg)
    {

        if (is_string($messageArg)) {
            $messageArg = [$messageArg];
        }

        if (empty($messageArg)
            || (!empty($this->_data[self::P_MESSAGE_ARG])
                && $this->_data[self::P_MESSAGE_ARG] === $messageArg)
        ) {
            return null;
        }

        $formattedMessage = vsprintf($this->message, $messageArg);

        if ($formattedMessage === $this->message) {
            foreach ($messageArg as $message) {
                $formattedMessage .= ' [' . $message . ']';
            }
        }
        $this->message = $formattedMessage;

        $this->_data[self::P_MESSAGE_ARG] = $messageArg;
    }

    /**
     * @param $key
     */
    public function unsetData($key)
    {
        unset($this->_data[$key]);
    }

    /**
     * @param      $globalCode
     * @param null $data
     *
     * @return mixed
     * @throws RuntimeException
     */
    public static function factory($globalCode, $data = null)
    {
        if (self::R_SUCCESS === $globalCode) {
            return new self($globalCode, $data);
        }

        $code = $globalCode % 1000;
        $resultClassIndex = (int)($globalCode / 1000);
        $resultClass = array_search($resultClassIndex,
            self::getClassIndexMap());

        if ($resultClass != null) {
            return new $resultClass($code, $data);
        }

        throw new RuntimeException("Unknown result code [$globalCode]");
    }

    /**
     * @return array
     * @throws InvalidArgumentException
     */
    public static function getClassIndexMap()
    {
        if (self::$resultClassIndexMap === null) {
            throw new InvalidArgumentException("Provide path to class index map file.");
        }

        return self::$resultClassIndexMap;
    }

    /**
     * @param $path string|array
     *
     * @throws RuntimeException
     */
    public static function setClassIndexMap($path)
    {
        if (is_array($path)) {
            self::$resultClassIndexMap = $path;
        } elseif (file_exists($path) && is_readable($path)) {
            self::$resultClassIndexMap = require_once($path);
        } else {
            throw new RuntimeException("Path $path does not exists or is not readable.");
        }
    }


    /**
     * @return string
     */
    public function __toString()
    {
        $result = parent::__toString();

        $result .= ' Global Code: ' . $this->getGlobalCode();
        $result .= ' Message: ' . $this->getAlias();

        return $result;
    }

    /**
     * @return int|string
     */
    public function getGlobalCode()
    {
        return $this->_globalCode;
    }


    /**
     * @param bool $packed
     *
     * @return array
     */
    public function toArray($packed = false)
    {
        $result = parent::toArray($packed);

        $result['data'] = $this->_data;
        $result['globalCode'] = $this->getGlobalCode();
        $result['messageAdm'] = $this->getMessageAdmin();

        return $result;
    }

    public function getMessageAdmin()
    {
        if ($this->_messageAdmin === null) {
            $this->_messageAdmin = self::buildMessageAdmin($this);
        }

        return $this->_messageAdmin;
    }

    protected function _setMessageAdmin($message)
    {
        $this->_messageAdmin = $message;
    }

    public static function buildMessageAdmin(ResultException $result)
    {
        $translateKey = self::TRANSLATE_KEY_PREFIX_ADMIN . $result->_globalCode;
        $message = self::translate($translateKey);

        return $message;
    }

    public function getInnerException()
    {
        return $this->_innerException;
    }

    /**
     * @param \Exception $exception
     */
    public function setInnerException(\Exception $exception)
    {
        $this->_innerException = $exception;
    }

    /**
     * @return bool
     */
    public function hasInnerException()
    {
        return !empty($this->_innerException);
    }

    /******************************************************************************
     * DATA
     ******************************************************************************/

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->_status;
    }

    /**
     * @param $status
     */
    public function setStatus($status)
    {
        $this->_status = $status;
    }

    /**
     * @return bool
     */
    public function isSuccess()
    {
        return ($this->_status === self::STATUS_SUCCESS);
    }

    /**
     * @return bool
     */
    public function isFailure()
    {
        return ($this->_status === self::STATUS_FAILURE);
    }

    /**
     * @return bool
     */
    public function isError()
    {
        return ($this->_status === self::STATUS_ERROR);
    }

    /**
     * @return bool
     */
    public function isIgnoreLogging()
    {
        return !empty($this->_data[self::P_IGNORE_LOGGING]);
    }

    /**
     *
     */
    public function clearData()
    {
        $this->_data = [];
    }

    /**
     * @return int
     */
    public function countData()
    {
        return count($this->_data);
    }

    /******************************************************************************
     * Translator
     ******************************************************************************/

    /**
     * Set function which provide translations
     *
     * @param $translator
     */
    public static function setTranslatorFunction($translator)
    {
        static::$_translatorFunction = $translator;
    }

    /**
     * Get translate function
     *
     * @return string
     */
    public static function getTranslatorFunction()
    {
        return static::$_translatorFunction;
    }

    /**
     * @param $key String
     *
     * @return string
     */
    protected static function translate($key)
    {
        if (function_exists(static::getTranslatorFunction())) {
            $translated = call_user_func(static::getTranslatorFunction(), $key);
            if ($translated != $key) {
                return $translated;
            }
        } else {
            return '';
        }
    }

    /******************************************************************************
     * MAGICs
     ******************************************************************************/

    /**
     * @param $key
     *
     * @return array|null
     */
    public function __get($key)
    {
        return $this->getData($key);
    }

    /**
     * @param $key
     * @param $value
     */
    public function __set($key, $value)
    {
        $this->setData($key, $value);
    }

    /**
     * @param $key
     *
     * @return bool
     */
    public function __isset($key)
    {
        return $this->hasData($key);
    }

    /**
     * @param $key
     */
    public function __unset($key)
    {
        $this->unsetData($key);
    }

    /******************************************************************************
     * ARRAY_ACCESS INTERFACE
     ******************************************************************************/

    /**
     * @param $offset
     *
     * @return bool
     */
    public function offsetExists($offset)
    {
        return $this->hasData($offset);
    }

    /**
     * @param $offset
     *
     * @return array|null
     */
    public function offsetGet($offset)
    {
        return $this->getData($offset);
    }

    /**
     * @param $offset
     * @param $value
     */
    public function offsetSet($offset, $value)
    {
        $this->setData($offset, $value);
    }

    /**
     * @param $offset
     */
    public function offsetUnset($offset)
    {
        $this->unsetData($offset);
    }
}
