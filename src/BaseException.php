<?php


namespace Result;

/**
 * @package Ik\Exception
 */
class BaseException extends \Exception implements ExceptionInterface
{
    public static $packKeyMap
        = [
            'class'    => 'c',
            'code'     => 'cd',
            'message'  => 'm',
            'file'     => 'f',
            'line'     => 'l',
            'trace'    => 't',
            'previous' => 'p',
        ];

    /**
     * @param string     $message
     * @param int        $code
     * @param \Exception $previous
     */
    public function __construct(
        $message = '',
        $code = 0,
        \Exception $previous = null
    ) {
        if ($code instanceof \Exception) {
            $previous = $code;
            $code = 0;
        } elseif (!is_numeric($code)) {
            $message = sprintf($message, $code);
        }

        parent::__construct($message, $code, $previous);
    }

    /**
     * @return string
     */
    public function toJson()
    {
        return json_encode($this->toArray());
    }

    /**
     * @param bool $packed
     *
     * @return array
     */
    public function toArray($packed = false)
    {
        $result = [
            'class'   => get_class($this),
            'code'    => $this->getCode(),
            'message' => $this->getMessage(),
            'file'    => $this->getFile(),
            'line'    => $this->getLine(),
        ];

        $previous = $this->getPrevious();
        if (!empty($previous)) {
            if ($previous instanceof BaseException) {
                $result['previous'] = $previous->toArray();
            } else {
                $result['previous'] = (array)$previous;
            }
        }

        if ($packed) {
            $result = array_combine(array_merge($result, self::$packKeyMap),
                $result);
        }

        return $result;
    }

    /**
     * @return mixed
     */
    public function toXml()
    {
        return \Ik\Lib\Xml::toXml($this->toArray());
    }
}
