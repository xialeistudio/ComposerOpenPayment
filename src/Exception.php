<?php
/**
 * Created by PhpStorm.
 * User: xialei
 * Date: 2017/3/16
 * Time: 16:28
 */

namespace payment;


class Exception extends \Exception
{
    /**
     * @var string 支付渠道
     */
    private $channel;

    /**
     * Exception constructor.
     * @param string $channel
     * @param string $message
     * @param integer $code
     * @param \Throwable|null $previous
     */
    public function __construct($channel, $message = '', $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->channel = $channel;
    }

    /**
     * 获取渠道名称
     * @return string
     */
    public function getChannel()
    {
        return $this->channel;
    }
}