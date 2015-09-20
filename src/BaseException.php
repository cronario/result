<?php


namespace Result;

/**
 * @package Cronario\Result
 */
class BaseException extends \Exception implements ExceptionInterface
{

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
            $code = 0;
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
     * @return array
     */
    public function toArray()
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

        return $result;
    }
}
