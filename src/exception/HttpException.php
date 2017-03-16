<?php
/**
 * Created by PhpStorm.
 * User: xialei
 * Date: 2017/3/16
 * Time: 16:52
 */

namespace payment\exception;


use payment\Exception;

/**
 * HTTP请求异常
 * Class HttpException
 * @package exception
 */
class HttpException extends Exception
{
    private $statusCode;

    public function __construct($channel, $statusCode, $message = '', $code = '', $previous = null)
    {
        parent::__construct($channel, $message, $code, $previous);
        $this->statusCode = $statusCode;
    }

    /**
     * @return string
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }
}