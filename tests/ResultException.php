<?php


namespace Result\Test;

class ResultException extends \Result\ResultException
{

    const STATUS_CUSTOM = 'custom';
    const TYPE_PREFIX = '';

    const TEST_E = 900;
    const TEST_STRING = 5;
    const TEST_TRANSLATE_STRING = 6;
    const TEST_IGNORE = 7;
    const TEST_ALIAS = 8;
    const TEST_RESULT = 9;
    const TEST_MSG_ARG = 10;
    const TEST_E_ADMIN_MESSAGE = 901;

    const TEST_PRESET_MSG_ARG = 11; //remove default Ik prefix

    public static $results
        = [
            self::TEST_E     => [
                self::P_STATUS => self::STATUS_CUSTOM
            ],
            //default results
            self::R_SUCCESS  => array(
                self::P_MESSAGE => 'Success',
                self::P_STATUS  => self::STATUS_SUCCESS
            ),
            self::R_FAILURE  => array(
                self::P_MESSAGE => 'Fail',
                self::P_STATUS  => self::STATUS_FAILURE,
            ),
            self::E_INTERNAL => array(
                self::P_MESSAGE => 'Internal error',
                self::P_STATUS  => self::STATUS_ERROR
            ),
            self::TEST_E_ADMIN_MESSAGE => array(
                self::P_MESSAGE => 'Internal error',
                self::P_STATUS  => self::STATUS_ERROR,
                self::P_MESSAGE_ADMIN => 'Internal message for admin with additional info'
            ),
            self::TEST_STRING => 'I am just string',
            self::TEST_IGNORE => array(
                self::P_MESSAGE => 'Foo',
                self::P_IGNORE_LOGGING => true
            ),
            self::TEST_ALIAS => array(
                self::P_ALIAS => 'Alias'
            ),
            self::TEST_MSG_ARG => array(
                self::P_MESSAGE => 'Test %s',
            ),
            self::TEST_PRESET_MSG_ARG => array(
                self::P_MESSAGE => 'Test %s',
                self::P_MESSAGE_ARG => 'argument'
            ),

        ];

    public function isCustomStatus()
    {
        return ($this->status === self::STATUS_CUSTOM);
    }

    public static function t($key)
    {
        return strtolower($key . '-translated');
    }

    public static function tUnchanged($key)
    {
        return $key;
    }
}
