<?php
/**
 * Created by PhpStorm.
 * User: xialei
 * Date: 2017/3/16
 * Time: 16:17
 */

namespace payment\channel\wx;


use payment\exception\HttpException;
use payment\exception\InvalidParamException;
use payment\OpenPayment;

/**
 * 微信支付
 * Class Payment
 * @package channel\wx
 */
class Payment extends OpenPayment
{
    /**
     * 微信接口地址
     */
    const BASE_URL = 'https://api.mch.weixin.qq.com';

    private $mchId;
    private $key;
    private $appId;
    private $appSecret;

    /**
     * Payment constructor.
     * @param string $mchId
     * @param string $key
     * @param string $appId
     * @param string $appSecret
     */
    public function __construct($mchId, $key, $appId, $appSecret)
    {
        $this->mchId = $mchId;
        $this->key = $key;
        $this->appId = $appId;
        $this->appSecret = $appSecret;
    }


    /**
     * XML转数组
     * @param string $xml
     * @return array
     * @throws InvalidParamException
     */
    public static function xml2array($xml)
    {
        if (empty($xml)) {
            throw new InvalidParamException(self::CHANNEL_WECHAT, 'XML错误');
        }
        libxml_disable_entity_loader(true);
        return json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), true);
    }

    /**
     * 数组转XML
     * @param array $data
     * @return string
     */
    public static function array2xml(array $data)
    {
        $xml = '<xml>';
        foreach ($data as $key => $value) {
            if (is_numeric($value)) {
                $xml .= "<{$key}>{$value}</{$key}>";
            } else {
                $xml .= "<{$key}><![CDATA[{$value}]]></{$key}>";
            }
        }
        $xml .= '</xml>';
        return $xml;
    }


    /**
     * 统一下单参数验证
     * @param Data $data
     * @throws InvalidParamException
     */
    private function prepayValidate(Data $data)
    {
        if ($data->getAppId() === null) {
            throw new InvalidParamException($this->getChannel(), 'AppId未设置');
        }
        if ($data->getMchId() === null) {
            throw new InvalidParamException($this->getChannel(), 'MchId未设置');
        }
        if ($data->getSign() === null) {
            throw new InvalidParamException($this->getChannel(), 'Sign未设置');
        }
        if ($data->getBody() === null) {
            throw new InvalidParamException($this->getChannel(), 'Body未设置');
        }
        if ($data->getOutTradeNo() === null) {
            throw new InvalidParamException($this->getChannel(), 'OutTradeNo未设置');
        }
        if ($data->getTotalFee() === null) {
            throw new InvalidParamException($this->getChannel(), 'TotalFee未设置');
        }
        if ($data->getSpbillCreateIp() === null) {
            throw new InvalidParamException($this->getChannel(), 'SpbillCreateIp未设置');
        }
        if ($data->getNotifyUrl() === null) {
            throw new InvalidParamException($this->getChannel(), 'NotifyUrl未设置');
        }
        if ($data->getTradeType() === null) {
            throw new InvalidParamException($this->getChannel(), 'TradeType未设置');
        }
    }

    /**
     * JSAPI支付时统一下单参数验证
     * @param Data $data
     * @throws InvalidParamException
     */
    private function prepayValidateWithJsApi(Data $data)
    {
        if ($data->getOpenid() === null) {
            throw new InvalidParamException(OpenPayment::CHANNEL_WECHAT, 'Openid未设置');
        }
    }

    /**
     * NATIVE支付时统一下单参数验证
     * @param Data $data
     * @throws InvalidParamException
     */
    private function prepayValidateWithNative(Data $data)
    {
        if ($data->getProductId() === null) {
            throw new InvalidParamException(OpenPayment::CHANNEL_WECHAT, 'ProductId未设置');
        }
    }

    /**
     * APP支付时统一下单参数验证
     * @param Data $data
     */
    private function prepayValidateWithApp(Data $data)
    {
    }

    /**
     * 统一HTTP请求
     * @param $api
     * @param Data $data
     * @return array
     * @throws HttpException
     */
    private function request($api, Data $data)
    {
        $xml = self::array2xml($data->getData());
        $headers = [
            'Content-Type' => 'text/xml'
        ];
        $resp = \Requests::post(self::BASE_URL . $api, $headers, $xml, $this->httpOptions);
        if ($resp->status_code !== 200) {
            throw new HttpException($resp->body, $resp->status_code);
        }
        $response = self::xml2array($resp->body);
        if ($response['return_code'] !== 'SUCCESS') {
            return $response;
        }
        return $response;
    }

    /**
     * @return string
     */
    public function getMchId()
    {
        return $this->mchId;
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @return string
     */
    public function getAppId()
    {
        return $this->appId;
    }

    /**
     * @return string
     */
    public function getAppSecret()
    {
        return $this->appSecret;
    }

    /**
     * 返回渠道名称
     * @return mixed
     */
    public function getChannel()
    {
        return OpenPayment::CHANNEL_WECHAT;
    }

    /**
     * 统一下单
     * @param Data $data
     * @return array
     */
    public function prepay(Data $data)
    {
        if ($data->getNonceStr() === null) {
            $data->setNonceStr($this->getNonceStr());
        }
        if ($data->getFeeType() === null) {
            $data->setFeeType(Data::FEE_TYPE_CNY);
        }
        $data->sign();
        $this->prepayValidate($data);
        if ($data->getTradeType() === Data::TRADE_TYPE_JSAPI) {
            $this->prepayValidateWithJsApi($data);
        }
        if ($data->getTradeType() === Data::TRADE_TYPE_NATIVE) {
            $this->prepayValidateWithNative($data);
        }
        if ($data->getTradeType() === Data::TRADE_TYPE_APP) {
            $this->prepayValidateWithApp($data);
        }

        return $this->request('/pay/unifiedorder', $data);
    }

    /**
     * 订单查询
     * @param Data $data
     * @return array
     */
    public function orderQuery(Data $data)
    {
        if ($data->getNonceStr() === null) {
            $data->setNonceStr($this->getNonceStr());
        }
        $data->sign();
        $this->orderQueryValidate($data);
        return $this->request('/pay/orderquery', $data);
    }

    /**
     * 订单查询验证参数
     * @param Data $data
     * @throws InvalidParamException
     */
    private function orderQueryValidate(Data $data)
    {
        if ($data->getAppId() === null) {
            throw new InvalidParamException($this->getChannel(), 'AppId未设置');
        }
        if ($data->getMchId() === null) {
            throw new InvalidParamException($this->getChannel(), 'MchId未设置');
        }
        if ($data->getSign() === null) {
            throw new InvalidParamException($this->getChannel(), 'Sign未设置');
        }
        if ($data->getSignType() === null) {
            throw new InvalidParamException($this->getChannel(), 'SignType未设置');
        }
        if ($data->getNonceStr() === null) {
            throw new InvalidParamException($this->getChannel(), 'NonceStr未设置');
        }
        if ($data->getTransactionId() === null && $data->getOutTradeNo() === null) {
            throw new InvalidParamException($this->getChannel(), '商户订单号和微信订单号不能同时为空');
        }
    }
}