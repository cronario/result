<?php


namespace Result\Test;

class ResultException extends \Result\ResultException
{

    const TEST_E = 900;
    const TEST_STRING = 5;
    const TEST_TRANSLATE_STRING = 6;
    const TEST_E_ADMIN_MESSAGE = 901;

    const STATUS_CUSTOM = 'custom';

    const TYPE_PREFIX = ''; //remove default Ik prefix

    public static $results
        = [
            self::TEST_E     => [
                self::P_STATUS => self::STATUS_CUSTOM
            ],
            //default results
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
                self::P_STATUS  => self::STATUS_ERROR
            ),
            self::TEST_E_ADMIN_MESSAGE => array(
                self::P_MESSAGE => 'Internal error',
                self::P_STATUS  => self::STATUS_ERROR,
                self::P_MESSAGE_ADMIN => 'Internal message for admin with additional info'
            ),
            self::TEST_STRING => 'I am just string'

        ];

    public function isCustomStatus()
    {
        return ($this->status === self::STATUS_CUSTOM);
    }

    public static function t($key)
    {
        return strtolower($key . '-translated');
    }
}
