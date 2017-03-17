<?php
/**
 * Created by PhpStorm.
 * User: xialei
 * Date: 2017/3/16
 * Time: 14:45
 */

namespace payment;

use payment\exception\HttpException;

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

    /**
     * 初始化CURL
     * @param $url
     * @param array $options
     * @return resource
     */
    protected function setupRequests($url, array $options = [])
    {
        $defaultOptions = [
            'timeout' => 30,
        ];
        $options = array_merge($defaultOptions, $options);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_TIMEOUT, $options['timeout']);
        // 代理
        if (isset($options['proxy'])) {
            $proxy = explode(':', $options['proxy']);
            curl_setopt($ch, CURLOPT_PROXY, $proxy[0]);
            curl_setopt($ch, CURLOPT_PROXYPORT, $proxy[1]);
        }
        // 设置CA
        if (isset($options['ssl_ca'])) {
            curl_setopt($ch, CURLOPT_CAINFO, $options['ssl_ca']);
        }
        // 校验SSL
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);//严格校验

        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // 双向SSL
        if (isset($options['ssl_cert'])) {
            curl_setopt($ch, CURLOPT_SSLCERTTYPE, 'PEM');
            curl_setopt($ch, CURLOPT_SSLCERT, $options['ssl_cert']);
        }
        if (isset($options['ssl_key'])) {
            curl_setopt($ch, CURLOPT_SSLKEYTYPE, 'PEM');
            curl_setopt($ch, CURLOPT_SSLKEY, $options['ssl_key']);
        }
        return $ch;
    }

    /**
     * 发送请求
     * @param $ch
     * @return mixed
     * @throws HttpException
     */
    protected function sendRequests($ch)
    {
        $data = curl_exec($ch);
        if ($data === false) {
            $error = curl_error($ch);
            $errno = curl_errno($ch);
            curl_close($ch);
            throw new HttpException(static::getChannel(), 500, "网络请求失败:{$error}", $errno);
        }
        curl_close($ch);
        return $data;
    }

    /**
     * GET请求
     * @param $url
     * @param array $options
     * @return mixed
     * @throws HttpException
     */
    protected function getRequests($url, array $options = [])
    {
        $ch = $this->setupRequests($url, $options);
        return $this->sendRequests($ch);
    }

    /**
     * POST请求
     * @param $url
     * @param $data
     * @param array $options
     * @return mixed
     */
    protected function postRequests($url, $data, array $options = [])
    {
        $ch = $this->setupRequests($url, $options);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        return $this->sendRequests($ch);
    }
}