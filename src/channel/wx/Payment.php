<?php
/**
 * Created by PhpStorm.
 * User: xialei
 * Date: 2017/3/16
 * Time: 16:17
 */

namespace payment\channel\wx;


use payment\exception\InvalidParamException;
use payment\exception\SignInvalidException;
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
     * @var string key文件路径
     */
    private $keyFile;
    /**
     * @var string cert文件路径
     */
    private $certFile;
    /**
     * @var string ca文件路径
     */
    private $caFile;

    /**
     * Payment constructor.
     * @param string $mchId 商户号
     * @param string $key 商户密钥
     * @param string $appId APPID
     * @param string $appSecret APP Secret
     */
    public function __construct($mchId, $key, $appId, $appSecret)
    {
        $this->mchId = $mchId;
        $this->key = $key;
        $this->appId = $appId;
        $this->appSecret = $appSecret;

        $this->caFile = __DIR__ . '/../cert/cacert.pem';
    }

    /**
     * @param mixed $keyFile
     */
    public function setKeyFile($keyFile)
    {
        $this->keyFile = $keyFile;
    }

    /**
     * @param mixed $certFile
     */
    public function setCertFile($certFile)
    {
        $this->certFile = $certFile;
    }

    /**
     * @param mixed $caFile
     */
    public function setCaFile($caFile)
    {
        $this->caFile = $caFile;
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
     * 统一HTTP请求
     * @param $api
     * @param Data $data
     * @param array $options
     * @return array|mixed
     * @throws SignInvalidException
     */
    private function request($api, Data $data, array $options = [])
    {
        $options = array_merge(['ssl_ca' => $this->caFile], $options);
        $xml = self::array2xml($data->getData());
        $data = $this->postRequests(self::BASE_URL . $api, $xml, $options);
        if (isset($options['raw']) && $options['raw'] === true && strpos($data, '<') !== 0) {
            return $data;
        }
        $response = self::xml2array($data);
        if (!isset($response['sign'])) {
            return $response;
        }
        // 签名验证
        $data = Data::initWithArray($this, $response);
        if ($data->getSign() !== $data->sign()) {
            throw new SignInvalidException($this->getChannel(), '签名错误');
        }
        return $response;
    }

    /**
     * 必须有的参数验证
     * @param Data $data
     * @throws InvalidParamException
     */
    private function commonValidate(Data $data)
    {
        if ($data->getMchId() === null) {
            throw new InvalidParamException($this->getChannel(), 'MchId未设置');
        }
        if ($data->getSign() === null) {
            throw new InvalidParamException($this->getChannel(), 'Sign未设置');
        }
        if ($data->getNonceStr() === null) {
            throw new InvalidParamException($this->getChannel(), 'NonceStr未设置');
        }
    }

    /**
     * 统一下单参数验证
     * @param Data $data
     * @throws InvalidParamException
     */
    private function prepayValidate(Data $data)
    {
        $this->commonValidate($data);
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
     * @throws InvalidParamException
     */
    public function orderQuery(Data $data)
    {
        if ($data->getNonceStr() === null) {
            $data->setNonceStr($this->getNonceStr());
        }
        if ($data->getTransactionId() === null && $data->getOutTradeNo() === null) {
            throw new InvalidParamException($this->getChannel(), '商户订单号和微信订单号不能同时为空');
        }
        $data->sign();
        $this->commonValidate($data);
        return $this->request('/pay/orderquery', $data);
    }

    /**
     * 关闭订单
     * @param Data $data
     * @return array
     * @throws InvalidParamException
     */
    public function closeOrder(Data $data)
    {
        if ($data->getNonceStr() === null) {
            $data->setNonceStr($this->getNonceStr());
        }
        if ($data->getOutTradeNo() === null) {
            throw new InvalidParamException($this->getChannel(), '商户订单号不能为空');
        }
        $data->sign();
        $this->commonValidate($data);
        return $this->request('/pay/closeorder', $data);
    }

    /**
     * 退款
     * @param Data $data
     * @return array
     * @throws InvalidParamException
     */
    public function refund(Data $data)
    {
        if ($data->getNonceStr() === null) {
            $data->setNonceStr($this->getNonceStr());
        }
        if ($data->getRefundFeeType() === null) {
            $data->setRefundFeeType(Data::FEE_TYPE_CNY);
        }
        if ($data->getOpUserId() === null) {
            $data->setOpUserId();
        }
        if ($data->getRefundAccount() === null) {
            $data->setRefundAccount(Data::REFUND_SOURCE_UNSETTLED_FUNDS);
        }
        if ($data->getTransactionId() === null && $data->getOutTradeNo() === null) {
            throw new InvalidParamException($this->getChannel(), '商户订单号和微信订单号不能同时为空');
        }
        if ($data->getOutRefundNo() === null) {
            throw new InvalidParamException($this->getChannel(), '商户退款单号不能为空');
        }
        if ($data->getTotalFee() === null) {
            throw new InvalidParamException($this->getChannel(), 'TotalFee未设置');
        }
        if ($data->getRefundFee() === null) {
            throw new InvalidParamException($this->getChannel(), 'RefundFee未设置');
        }
        if (empty($this->certFile) || empty($this->caFile) || empty($this->keyFile)) {
            throw new InvalidParamException($this->getChannel(), '证书未设置');
        }
        $data->sign();
        $this->commonValidate($data);
        return $this->request('/secapi/pay/refund', $data, [
            'ssl_key' => $this->keyFile,
            'ssl_cert' => $this->certFile,
        ]);
    }

    /**
     * 查询退款
     * @param Data $data
     * @return array
     * @throws InvalidParamException
     */
    public function queryRefund(Data $data)
    {
        if ($data->getNonceStr() === null) {
            $data->setNonceStr($this->getNonceStr());
        }
        if ($data->getTransactionId() === null
            && $data->getOutTradeNo() === null
            && $data->getRefundId() === null
            && $data->getOutRefundNo() === null
        ) {
            throw new InvalidParamException($this->getChannel(), '商户订单号、微信订单号、商户退款单号和微信退款单号不能同时为空');
        }
        $data->sign();
        $this->commonValidate($data);
        return $this->request('/pay/refundquery', $data);
    }

    /**
     * 下载对账单
     * @param Data $data
     * @param bool $gzip
     * @return array
     * @throws InvalidParamException
     */
    public function downloadBill(Data $data, $gzip = true)
    {
        if ($data->getNonceStr() === null) {
            $data->setNonceStr($this->getNonceStr());
        }
        if ($data->getBillType() === null) {
            $data->setBillType(Data::BILL_TYPE_ALL);
        }
        if ($gzip === true) {
            $data->setTarType();
        }
        if ($data->getBillDate() === null) {
            throw new InvalidParamException($this->getChannel(), '对账日期不能为空');
        }
        $data->sign();
        $this->commonValidate($data);
        return $this->request('/pay/downloadbill', $data, [
            'raw' => true,
        ]);
    }

    /**
     * 检测XML文件签名
     * @param string $xml
     * @throws SignInvalidException
     */
    public function validateSign($xml)
    {
        $array = static::xml2array($xml);
        $data = Data::initWithArray($this, $array);
        if ($data->getSign() !== $data->sign()) {
            throw new SignInvalidException($this->getChannel(), '签名错误');
        }
    }

    /**
     * 获取XML
     * @param string $message 返回信息，如非空，为错误原因：如签名失败，参数格式校验错误
     * @param string $code SUCCESS/FAIL
     * @return string
     */
    public function getReply($message, $code)
    {
        $data = [
            'return_code' => $code,
            'return_msg' => $message
        ];
        return static::array2xml($data);
    }

    /**
     * 获取微信支付参数
     * @param $prepayId
     * @return array
     */
    public function getJsApiParameters($prepayId)
    {
        $time = strval(time());
        $nonceStr = $this->getNonceStr();
        $data = [
            'appId' => $this->appId,
            'timeStamp' => $time,
            'nonceStr' => $nonceStr,
            'package' => "prepay_id={$prepayId}",
            'signType' => 'MD5'
        ];
        $pkg = new Data($this);
        $pkg->setData($data);
        $sign = $pkg->sign(false);
        $data['paySign'] = $sign;
        return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * 发送普通红包
     * @param Data $data
     * @return array|mixed
     * @throws InvalidParamException
     */
    public function sendRedPack(Data $data)
    {
        $data->setAppId(null);
        $data->setSignType(null);
        if ($data->getNonceStr() === null) {
            $data->setNonceStr($this->getNonceStr());
        }
        if ($data->getMchBillNo() === null) {
            throw new InvalidParamException($this->getChannel(), '商户订单号不能为空');
        }
        if ($data->getWxAppId() === null) {
            $data->setWxAppId($this->appId);
        }
        $data->sign();
        $this->commonValidate($data);
        return $this->request('/mmpaymkttransfers/sendredpack', $data, [
            'ssl_key' => $this->keyFile,
            'ssl_cert' => $this->certFile,
        ]);
    }

    /**
     * 发送裂变红包
     * @param Data $data
     * @return array|mixed
     * @throws InvalidParamException
     */
    public function sendGroupRedPack(Data $data)
    {
        $data->setAppId(null);
        $data->setSignType(null);
        if ($data->getNonceStr() === null) {
            $data->setNonceStr($this->getNonceStr());
        }
        if ($data->getMchBillNo() === null) {
            throw new InvalidParamException($this->getChannel(), '商户订单号不能为空');
        }
        if ($data->getWxAppId() === null) {
            $data->setWxAppId($this->appId);
        }
        if ($data->getAmtType() === null) {
            $data->setAmtType();
        }
        $data->sign();
        $this->commonValidate($data);
        return $this->request('/mmpaymkttransfers/sendgroupredpack', $data, [
            'ssl_key' => $this->keyFile,
            'ssl_cert' => $this->certFile,
        ]);
    }
}