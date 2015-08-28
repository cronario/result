<?php

namespace Result;


class ParameterException extends ResultException
{
    const E_PARAM_NOT_SET = 101;
    const E_PARAM_IS_EMPTY = 102;
    const E_PARAM_INVALID = 103;
    const E_PARAM_INCORRECT_FORMAT = 104;
    const E_PARAM_SET_FORBIDDEN = 105;

    /**
     * @var array
     */
    public static $results
        = array(
            self::E_PARAM_NOT_SET          => 'Parameter "%s" is not set',
            self::E_PARAM_IS_EMPTY         => 'Parameter "%s" is empty',
            self::E_PARAM_INVALID          => 'Parameter "%s" is invalid',
            self::E_PARAM_INCORRECT_FORMAT => 'Parameter "%s" has incorrect format',
            self::E_PARAM_SET_FORBIDDEN    => 'Parameter set "%s" forbidden',
        );
}
