<?php


namespace Result;

use ArrayAccess;


/**
 * Class ResultException
 *
 * @package Cronario\Result
 */
class ResultException extends BaseException implements ArrayAccess, ExceptionInterface
{
    /******************************************************************************
     * RESULTS
     ******************************************************************************/

    const R_SUCCESS = 0;
    const R_FAILURE = 2;
    const E_INTERNAL = 4;
    const P_ALIAS = 'alias';
    const P_MESSAGE = 'message';
    const P_MESSAGE_ADMIN = 'messageAdmin';
    const P_MESSAGE_ARG = 'messageArg';
    const P_STATUS = 'status';
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

    public static $resultClassIndexMap;
    /**
     * @var array
     */
    public static $results
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

    protected static $reflections;
    protected static $reflectionConstants;
    protected static $translatorFunction = '_t';

    /**
     * @var array Arguments which will be unset from result data
     */
    protected $serviceArguments = [self::P_STATUS, self::P_MESSAGE];

    protected $messageAdmin;
    /**
     * Converts any exception to internal error ResultException
     *
     * @var int
     */
    protected $globalCode;
    /******************************************************************************
     * EXCEPTION
     ******************************************************************************/

    protected $innerException;
    /**
     * @var string|array
     */
    protected $status = self::STATUS_FAILURE;
    /**
     * @var array
     */
    protected $data = [];

    /**
     * @param string|int|\Exception $code
     * @param null $data
     * @param \Exception|null $innerException
     *
     * @throws InvalidArgumentException
     * @throws OutOfRangeException
     */
    public function __construct(
        $code,
        $data = null,
        \Exception $innerException = null
    ) {
        if ($code === null) {
            throw new InvalidArgumentException("Empty result code arg");
        } elseif (is_int($code)) {
            $this->extractResultData($code);
        } elseif ($code instanceof \Exception) {
            $this->extractDataFromException($code);
            return $this;
        }

        $this->checkResultCode($code);

        $this->globalCode = self::buildGlobalCode($this);

        $this->initMessage();
        $this->initResultData($data);

        $this->initInnerException($innerException);

        $this->unsetData($this->serviceArguments);
        parent::__construct($this->getMessage(), $this->getCode(), $this->getInnerException());
    }

    /**
     * @param $results
     */
    protected function initData(&$results)
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
        $data = (array) $data;

        if (empty($data)) {
            return $this;
        }

        foreach ($data as $key => $value) {
            $this->setData($key, $value);
        }

        return $this;
    }

    /**
     * @param ResultException|string $result Result instance or class name
     * @param null|int $code
     *
     * @return int
     */
    public static function buildGlobalCode($result, $code = null)
    {
        if ($result instanceof self) {
            $resultClass = get_class($result);
            $resultCode = $result->getCode();
        } else {
            $resultClass = (string) $result;
            $resultCode = (int) $code;
        }

        if (self::R_SUCCESS === $resultCode) {
            return self::R_SUCCESS;
        }

        $resultClassIndex = self::getClassIndex($resultClass);

        $resultCode = ($resultClassIndex * 1000) + $resultCode;

        return (int) $resultCode;
    }

    /** Returns class index by class name
     *
     * @param string $class Name of class
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
     * @param string $key
     *
     * @return mixed
     */
    public function getConstant($key)
    {
        $className = get_class($this);

        if (!isset(self::$reflectionConstants[$className])) {
            self::$reflectionConstants[$className] = self::getReflection($this)->getConstants();
        }

        return array_search($key, self::$reflectionConstants[$className], true);
    }

    /**
     * @param ResultException $object
     *
     * @return mixed
     */
    protected static function getReflection($object)
    {
        $reflectionClass = get_class($object);

        if (!isset(self::$reflections[$reflectionClass])) {
            self::$reflections[$reflectionClass]
                = new \ReflectionClass($object);
        }

        return self::$reflections[$reflectionClass];
    }

    /**
     * Try to translate message using translator,
     * otherwise returns alias or constant name
     *
     * @param ResultException $result
     *
     * @return string $message Result message
     */
    public static function buildMessage(ResultException $result)
    {
        $translateKey = self::TRANSLATE_KEY_PREFIX . $result->globalCode;
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
            return $this->getConstant($this->code);
        }
    }

    /**
     * @param $key
     *
     * @return bool
     */
    public function hasData($key)
    {
        return isset($this->data[$key]);
    }

    /**
     * @param null $key
     * @param null $default
     *
     * @return array|null|string
     */
    public function getData($key = null, $default = null)
    {
        if ($key === null) {
            return $this->data;
        }

        return isset($this->data[$key]) ? $this->data[$key] : $default;
    }

    /**
     * @param $key
     * @param $value
     *
     * @return $this
     */
    public function setData($key, $value)
    {
        if (property_exists($this, $key)) {
            return $this->{$key} = $value;
        }

        /**
         * This should be after initialized message
         */
        if ($key === self::P_MESSAGE_ARG) {
            return $this->setMessageArg($value);
        }

        $this->data[$key] = $value;

        return $this;
    }

    /**
     * @param $messageArg array|string
     *
     * @return $this|null
     */
    public function setMessageArg($messageArg)
    {

        $messageArg = (array) $messageArg;

        /**
         * Check on empty argument and check if
         * passed argument is not equal to existence
         */
        if (empty($messageArg)
            || $this->getData(self::P_MESSAGE_ARG) === $messageArg
        ) {
            return null;
        }

        $formattedMessage = vsprintf($this->message, $messageArg);

        if ($formattedMessage === $this->message) {
            // there are no any %s so we just append params
            $formattedMessage = ' [' . implode('] [', $messageArg) . ']';
        }
        $this->message = $formattedMessage;

        $this->data[self::P_MESSAGE_ARG] = $messageArg;

        return $this;
    }

    /**
     * @param array|string $key Key or keys to be unset
     */
    public function unsetData($key)
    {
        $keys = (array) $key;
        foreach ($keys as $key) {
            unset($this->data[$key]);
        }
    }

    /**
     * @param $globalCode
     * @param null $data
     *
     * @return ResultException
     * @throws InvalidArgumentException
     */
    public static function factory($globalCode, $data = null)
    {
        if (self::R_SUCCESS === $globalCode) {
            return new self($globalCode, $data);
        }

        $code = $globalCode % 1000;
        $resultClassIndex = (int) ($globalCode / 1000);
        $resultClass = array_search($resultClassIndex,
            self::getClassIndexMap());

        if ($resultClass != null) {
            return new $resultClass($code, $data);
        }

        throw new InvalidArgumentException("Unknown result code [$globalCode]");
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
     * @return int
     */
    public function getGlobalCode()
    {
        return $this->globalCode;
    }


    /**
     *
     * @return array
     */
    public function toArray()
    {
        $result = parent::toArray();

        $result['data'] = $this->data;
        $result['globalCode'] = $this->getGlobalCode();
        $result['messageAdm'] = $this->getMessageAdmin();

        return $result;
    }

    /**
     * @return string
     */
    public function getMessageAdmin()
    {
        if ($this->messageAdmin === null) {
            $this->messageAdmin = self::buildMessageAdmin($this);
        }

        return $this->messageAdmin;
    }

    /**
     * @param string $message Message for admin
     */
    public function setMessageAdmin($message)
    {
        $this->messageAdmin = $message;
    }

    public static function buildMessageAdmin(ResultException $result)
    {
        $translateKey = self::TRANSLATE_KEY_PREFIX_ADMIN . $result->globalCode;
        $message = self::translate($translateKey);

        return $message;
    }

    /**
     * @return \Exception
     */
    public function getInnerException()
    {
        return $this->innerException;
    }

    /**
     * @param \Exception $exception
     */
    public function setInnerException(\Exception $exception)
    {
        $this->innerException = $exception;
    }

    /**
     * @return bool
     */
    public function hasInnerException()
    {
        return !empty($this->innerException);
    }

    /******************************************************************************
     * DATA
     ******************************************************************************/

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return bool
     */
    public function isSuccess()
    {
        return ($this->status === self::STATUS_SUCCESS);
    }

    /**
     * @return bool
     */
    public function isFailure()
    {
        return ($this->status === self::STATUS_FAILURE);
    }

    /**
     * @return bool
     */
    public function isError()
    {
        return ($this->status === self::STATUS_ERROR);
    }

    /**
     * @return bool
     */
    public function isIgnoreLogging()
    {
        return !empty($this->data[self::P_IGNORE_LOGGING]);
    }


    public function clearData()
    {
        $this->data = [];
    }

    /**
     * @return int
     */
    public function countData()
    {
        return count($this->data);
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
        static::$translatorFunction = $translator;
    }

    /**
     * Get translate function
     *
     * @return string
     */
    public static function getTranslatorFunction()
    {
        return static::$translatorFunction;
    }

    /**
     * @param string $key String
     *
     * @return string
     */
    protected static function translate($key)
    {
        if (is_callable(static::getTranslatorFunction())) {
            $translated = call_user_func(static::getTranslatorFunction(), $key);
            if ($key !== $translated) {
                return $translated;
            } else {
                return '';
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

    /**
     * @param int $code Result code
     */
    protected function extractResultData($code)
    {
        $this->code = $code;
        if (isset(static::$results[$code])
            && self::R_SUCCESS !== $code
        ) {
            $this->initData(static::$results);
        } elseif (isset(self::$results[$code])) {
            $this->initData(self::$results);
        }
    }

    /**
     * @param \Exception $code
     */
    protected function extractDataFromException($code)
    {
        $this->status = self::STATUS_ERROR;
        $this->code = self::E_INTERNAL;
        $this->message = $code->getMessage();
        $this->setData('innerCode', $code->getCode());
        $this->globalCode = self::buildGlobalCode($this);
    }

    /**
     * @param $data
     */
    protected function initResultData($data)
    {
        if (is_array($data)) {
            $this->addData($data);
        } elseif (is_string($data)) {
            $this->setMessageArg($data);
        } elseif ($data instanceof \Exception) {
            $this->setInnerException($data);
        }
    }

    /**
     * @param $code
     *
     * @throws InvalidArgumentException
     * @throws OutOfRangeException
     */
    protected function checkResultCode($code)
    {
        if ($this->code > 1000) {
            throw new OutOfRangeException("Result code [$code] out of range");
        } elseif (false === $this->getConstant($this->code)) {
            throw new InvalidArgumentException("Invalid result code [$code]");
        }
    }

    /**
     * Init message if it was not present in result data
     */
    protected function initMessage()
    {
        if (empty($this->message)) {
            $this->message = self::buildMessage($this);
        }
    }

    protected function initInnerException($innerException)
    {
        if ($innerException instanceof \Exception) {
            $this->setInnerException($innerException);
        }
    }
}
