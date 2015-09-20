<?php


namespace Result;

class HttpException extends ResultException
{
    const HTTP_CONTINUE = 100;
    const HTTP_SWITCHING_PROTOCOLS = 101;

    const HTTP_OK = 200;
    const HTTP_CREATED = 201;
    const HTTP_ACCEPTED = 202;
    const HTTP_NON_AUTHORITATIVE_INFORMATION = 203;
    const HTTP_NO_CONTENT = 204;
    const HTTP_RESET_CONTENT = 205;
    const HTTP_PARTIAL_CONTENT = 206;

    const HTTP_MULTIPLE_CHOICES = 300;
    const HTTP_MOVED_PERMANENTLY = 301;
    const HTTP_FOUND = 302;
    const HTTP_SEE_OTHER = 303;
    const HTTP_NOT_MODIFIED = 304;
    const HTTP_USE_PROXY = 305;
    const HTTP_UNUSED = 306;
    const HTTP_TEMPORARY_REDIRECT = 307;

    const HTTP_BAD_REQUEST = 400;
    const HTTP_UNAUTHORIZED = 401;
    const HTTP_PAYMENT_REQUIRED = 402;
    const HTTP_FORBIDDEN = 403;
    const HTTP_NOT_FOUND = 404;
    const HTTP_METHOD_NOT_ALLOWED = 405;
    const HTTP_NOT_ACCEPTABLE = 406;
    const HTTP_PROXY_AUTHENTICATION_REQUIRED = 407;
    const HTTP_REQUEST_TIMEOUT = 408;
    const HTTP_CONFLICT = 409;
    const HTTP_GONE = 410;
    const HTTP_LENGTH_REQUIRED = 411;
    const HTTP_PRECONDITION_FAILED = 412;
    const HTTP_REQUEST_ENTITY_TOO_LARGE = 413;
    const HTTP_REQUEST_URI_TOO_LONG = 414;
    const HTTP_UNSUPPORTED_MEDIA_TYPE = 415;
    const HTTP_REQUESTED_RANGE_NOT_SATISFIABLE = 416;
    const HTTP_EXPECTATION_FAILED = 417;

    const HTTP_INTERNAL_SERVER_ERROR = 500;
    const HTTP_NOT_IMPLEMENTED = 501;
    const HTTP_BAD_GATEWAY = 502;
    const HTTP_SERVICE_UNAVAILABLE = 503;
    const HTTP_GATEWAY_TIMEOUT = 504;
    const HTTP_VERSION_NOT_SUPPORTED = 505;

    public static $results
        = array(
            self::HTTP_CONTINUE                        => array(
                'msg'      => 'Continue',
                'httpCode' => 100,
            ),
            self::HTTP_SWITCHING_PROTOCOLS             => array(
                'msg'      => 'Switching Protocols',
                'httpCode' => 101,
            ),
            self::HTTP_OK                              => array(
                'msg'      => 'OK',
                'httpCode' => 200,
            ),
            self::HTTP_CREATED                         => array(
                'msg'      => 'Created',
                'httpCode' => 201,
            ),
            self::HTTP_ACCEPTED                        => array(
                'msg'      => 'Accepted',
                'httpCode' => 202,
            ),
            self::HTTP_NON_AUTHORITATIVE_INFORMATION    => array(
                'msg'      => 'Non-Authoritative Information',
                'httpCode' => 203,
            ),
            self::HTTP_NO_CONTENT                      => array(
                'msg'      => 'No Content',
                'httpCode' => 204,
            ),
            self::HTTP_RESET_CONTENT                   => array(
                'msg'      => 'Reset Content',
                'httpCode' => 205,
            ),
            self::HTTP_PARTIAL_CONTENT                 => array(
                'msg'      => 'Partial Content',
                'httpCode' => 206,
            ),

            self::HTTP_MULTIPLE_CHOICES                => array(
                'msg'      => 'Multiple Choices',
                'httpCode' => 300,
            ),
            self::HTTP_MOVED_PERMANENTLY               => array(
                'msg'      => 'Moved Permanently',
                'httpCode' => 301,
            ),
            self::HTTP_FOUND                           => array(
                'msg'      => 'Found',
                'httpCode' => 302,
            ),
            self::HTTP_SEE_OTHER                       => array(
                'msg'      => 'See Other',
                'httpCode' => 303,
            ),
            self::HTTP_NOT_MODIFIED                    => array(
                'msg'      => 'Not Modified',
                'httpCode' => 304,
            ),
            self::HTTP_USE_PROXY                       => array(
                'msg'      => 'Use Proxy',
                'httpCode' => 305,
            ),
            self::HTTP_UNUSED                          => array(
                'msg'      => '(Unused)',
                'httpCode' => 306,
            ),
            self::HTTP_TEMPORARY_REDIRECT              => array(
                'msg'      => 'Temporary Redirect',
                'httpCode' => 307,
            ),

            self::HTTP_BAD_REQUEST                     => array(
                'msg'      => 'Bad Request',
                'httpCode' => 400,
            ),
            self::HTTP_UNAUTHORIZED                    => array(
                'msg'      => 'Unauthorized',
                'httpCode' => 401,
            ),
            self::HTTP_PAYMENT_REQUIRED                => array(
                'msg'      => 'Payment Required',
                'httpCode' => 402,
            ),
            self::HTTP_FORBIDDEN                       => array(
                'msg'      => 'Forbidden',
                'httpCode' => 403,
            ),
            self::HTTP_NOT_FOUND                       => array(
                'msg'      => 'Not Found',
                'httpCode' => 404,
            ),
            self::HTTP_METHOD_NOT_ALLOWED              => array(
                'msg'      => 'Method Not Allowed',
                'httpCode' => 405,
            ),
            self::HTTP_NOT_ACCEPTABLE                  => array(
                'msg'      => 'Not Acceptable',
                'httpCode' => 406,
            ),
            self::HTTP_PROXY_AUTHENTICATION_REQUIRED   => array(
                'msg'      => 'Proxy Authentication Required',
                'httpCode' => 407,
            ),
            self::HTTP_REQUEST_TIMEOUT                 => array(
                'msg'      => 'Request Timeout',
                'httpCode' => 408,
            ),
            self::HTTP_CONFLICT                        => array(
                'msg'      => 'Conflict',
                'httpCode' => 409,
            ),
            self::HTTP_GONE                            => array(
                'msg'      => 'Gone',
                'httpCode' => 410,
            ),
            self::HTTP_LENGTH_REQUIRED                 => array(
                'msg'      => 'Length Required',
                'httpCode' => 411,
            ),
            self::HTTP_PRECONDITION_FAILED             => array(
                'msg'      => 'Precondition Failed',
                'httpCode' => 412,
            ),
            self::HTTP_REQUEST_ENTITY_TOO_LARGE        => array(
                'msg'      => 'Request Entity Too Large',
                'httpCode' => 413,
            ),
            self::HTTP_REQUEST_URI_TOO_LONG            => array(
                'msg'      => 'Request-URI Too Long',
                'httpCode' => 414,
            ),
            self::HTTP_UNSUPPORTED_MEDIA_TYPE          => array(
                'msg'      => 'Unsupported Media Type',
                'httpCode' => 415,
            ),
            self::HTTP_REQUESTED_RANGE_NOT_SATISFIABLE => array(
                'msg'      => 'Requested Range Not Satisfiable',
                'httpCode' => 416,
            ),
            self::HTTP_EXPECTATION_FAILED              => array(
                'msg'      => 'Expectation Failed',
                'httpCode' => 417,
            ),
            self::HTTP_INTERNAL_SERVER_ERROR           => array(
                'msg'      => 'Internal Server Error',
                'httpCode' => 500,
            ),
            self::HTTP_NOT_IMPLEMENTED                 => array(
                'msg'      => 'Not Implemented',
                'httpCode' => 501,
            ),
            self::HTTP_BAD_GATEWAY                     => array(
                'msg'      => 'Bad Gateway',
                'httpCode' => 502,
            ),
            self::HTTP_SERVICE_UNAVAILABLE             => array(
                'msg'      => 'Service Unavailable',
                'httpCode' => 503,
            ),
            self::HTTP_GATEWAY_TIMEOUT                 => array(
                'msg'      => 'Gateway Timeout',
                'httpCode' => 504,
            ),
            self::HTTP_VERSION_NOT_SUPPORTED           => array(
                'msg'      => 'HTTP Version Not Supported',
                'httpCode' => 505,
            ),
        );
}
