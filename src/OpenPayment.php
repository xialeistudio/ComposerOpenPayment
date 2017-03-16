<?php
/**
 * Created by PhpStorm.
 * User: xialei
 * Date: 2017/3/16
 * Time: 14:45
 */

namespace payment;

/**
 * 支付
 * Class OpenPayment
 * @package payment
 */
abstract class OpenPayment
{
    // 版本名称
    const VERSION_NAME = '1.0.0';
    // 版本号，根据日期更新
    const VERSION_CODE = 20170316;
    // 渠道
    const CHANNEL_WECHAT = 'WECHAT'; // 微信支付
    const CHANNEL_ALIPAY = 'ALIPAY'; // 支付宝

    // http请求选项，比如设置证书，设置代理
    protected $httpOptions = [];

    /**
     * 返回渠道名称
     * @return mixed
     */
    abstract protected function getChannel();

    /**
     * 设置HTTP请求选项
     * @param array $httpOptions
     */
    public function setHttpOptions($httpOptions)
    {
        $this->httpOptions = array_merge($this->httpOptions, $httpOptions);
    }

    /**
     * 随机字符串
     * @param int $size
     * @return string
     */
    public function getNonceStr($size = 32)
    {
        $chars = '1234567890qwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM';
        $string = '';
        $length = strlen($chars);
        for ($i = 0; $i < $size; $i++) {
            $string .= $chars[rand(0, $length - 1)];
        }
        return $string;
    }
}