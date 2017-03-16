<?php
/**
 * Created by PhpStorm.
 * User: xialei
 * Date: 2017/3/16
 * Time: 15:06
 */

namespace payment\channel\wx;

/**
 * 微信交互数据封装
 * Class Data
 * @package library\wx
 * @inheritdoc https://pay.weixin.qq.com/wiki/doc/api/jsapi.php?chapter=9_1#
 */
class Data
{
    // 签名算法
    const SIGN_TYPE_MD5 = 'MD5';
    const SIGN_TYPE_HMAC_SHA256 = 'HMAC-SHA256';
    // 货币类型
    const FEE_TYPE_CNY = 'CNY'; // 人民币
    // 交易类型
    const TRADE_TYPE_JSAPI = 'JSAPI'; // 公众号支付
    const TRADE_TYPE_APP = 'APP'; // APP支付
    const TRADE_TYPE_NATIVE = 'NATIVE'; // 扫码支付
    /**
     * 请求参数
     * @var array
     */
    protected $data = [];

    /**
     * @var Payment|null
     */
    private $payment = null;

    /**
     * 构造方法
     * Data constructor.
     * @param Payment $payment
     */
    public function __construct(Payment $payment)
    {
        $this->payment = $payment;
        $this->setAppId($payment->getAppId());
        $this->setMchId($payment->getMchId());
        $this->setSignType(self::SIGN_TYPE_MD5);
    }


    /**
     * 设置APPID 微信支付分配的公众账号ID（企业号corpid即为此appId）
     * @param string $appId
     * @return $this
     */
    public function setAppId($appId)
    {
        $this->data['appid'] = $appId;
        return $this;
    }

    /**
     * 获取APPID
     * @return mixed|null
     */
    public function getAppId()
    {
        return isset($this->data['appid']) ? $this->data['appid'] : null;
    }

    /**
     * 设置商户号 微信支付分配的商户号
     * @param string $mchId
     * @return $this
     */
    public function setMchId($mchId)
    {
        $this->data['mch_id'] = $mchId;
        return $this;
    }

    /**
     * 获取商户号
     * @return mixed|null
     */
    public function getMchId()
    {
        return isset($this->data['mch_id']) ? $this->data['mch_id'] : null;
    }

    /**
     * 设置设备号 可以为终端设备号(门店号或收银设备ID)，PC网页或公众号内支付可以传"WEB"
     * @param string $deviceInfo
     * @return $this
     */
    public function setDeviceInfo($deviceInfo)
    {
        $this->data['device_info'] = $deviceInfo;
        return $this;
    }

    /**
     * 获取设备号
     * @return mixed|null
     */
    public function getDeviceInfo()
    {
        return isset($this->data['device_info']) ? $this->data['device_info'] : null;
    }

    /**
     * 设置随机字符串 长度要求在32位以内。
     * @param string $nonceStr
     * @return $this
     */
    public function setNonceStr($nonceStr)
    {
        $this->data['nonce_str'] = $nonceStr;
        return $this;
    }

    /**
     * 获取设置的随机字符串
     * @return mixed|null
     */
    public function getNonceStr()
    {
        return isset($this->data['nonce_str']) ? $this->data['nonce_str'] : null;
    }

    /**
     * 设置签名 通过签名算法计算得出的签名值
     * @param string $sign
     * @return $this
     */
    public function setSign($sign)
    {
        $this->data['sign'] = $sign;
        return $this;
    }

    /**
     * 获取签名
     * @return mixed|null
     */
    public function getSign()
    {
        return isset($this->data['sign']) ? $this->data['sign'] : null;
    }

    /**
     * 计算签名，微信官方文档尚未制定HMAC-SHA256签名时使用的key，目前仅支持MD5
     * @return string
     */
    public function sign()
    {
        // 清空当前签名
        $this->setSign(null);
        $key = $this->payment->getKey();
        // 过滤null参数
        $this->data = array_filter($this->data, function ($item) {
            return $item !== null;
        });
        // 排序
        ksort($this->data, SORT_STRING | SORT_ASC);
        $string = urldecode(http_build_query($this->data));
        // 拼接商户密钥
        $string = $string . '&key=' . $key;
        // 签名
        $sign = md5($string);
        // 签名大写
        $sign = strtoupper($sign);
        // 设置签名
        $this->setSign($sign);
        return $sign;
    }

    /**
     * 设置签名类型 默认为MD5，支持HMAC-SHA256和MD5。
     * @param string $signType
     * @return $this
     */
    public function setSignType($signType)
    {
        assert(in_array($signType, [self::SIGN_TYPE_MD5, self::SIGN_TYPE_HMAC_SHA256]), 'sign_type为MD5或HMAC-SHA256');
        $this->data['sign_type'] = $signType;
        return $this;
    }

    /**
     * 获取签名类型
     * @return mixed|null
     */
    public function getSignType()
    {
        return isset($this->data['sign_type']) ? $this->data['sign_type'] : null;
    }

    /**
     * 设置商品描述
     * @param string $body
     * @return $this
     */
    public function setBody($body)
    {
        $this->data['body'] = $body;
        return $this;
    }

    /**
     * 获取商品描述
     * @return mixed|null
     */
    public function getBody()
    {
        return isset($this->data['body']) ? $this->data['body'] : null;
    }

    /**
     * 设置商品详情 单品优惠字段(暂未上线)
     * @param string $detail
     * @return $this
     */
    public function setDetail($detail)
    {
        $this->data['detail'] = $detail;
        return $this;
    }

    /**
     * 获取商品详情
     * @return mixed|null
     */
    public function getDetail()
    {
        return isset($this->data['detail']) ? $this->data['detail'] : null;
    }

    /**
     * 附加数据 在查询API和支付通知中原样返回，可作为自定义参数使用。
     * @param string $attach
     * @return $this
     */
    public function setAttach($attach)
    {
        $this->data['attach'] = $attach;
        return $this;
    }

    /**
     * 获取附加数据
     * @return mixed|null
     */
    public function getAttach()
    {
        return isset($this->data['attach']) ? $this->data['attach'] : null;
    }

    /**
     * 商户订单号 要求32个字符内、且在同一个商户号下唯一。
     * @param $orderId
     * @return $this
     */
    public function setOutTradeNo($orderId)
    {
        $this->data['out_trade_no'] = $orderId;
        return $this;
    }

    /**
     * 获取商户订单号
     * @return mixed|null
     */
    public function getOutTradeNo()
    {
        return isset($this->data['out_trade_no']) ? $this->data['out_trade_no'] : null;
    }

    /**
     * 标价币种 符合ISO 4217标准的三位字母代码，默认人民币：CNY
     * @inheritdoc https://pay.weixin.qq.com/wiki/doc/api/jsapi.php?chapter=4_2
     * @param string $feeType
     * @return $this
     */
    public function setFeeType($feeType)
    {
        $this->data['fee_type'] = $feeType;
        return $this;
    }

    /**
     * 获取标价币种
     * @return mixed|null
     */
    public function getFeeType()
    {
        return isset($this->data['fee_type']) ? $this->data['fee_type'] : null;
    }

    /**
     * 设置标价金额
     * @param int $totalFee
     * @return $this
     */
    public function setTotalFee($totalFee)
    {
        assert(is_int($totalFee) && $totalFee > 0, 'total_fee为正整数');
        $this->data['total_fee'] = $totalFee;
        return $this;
    }

    /**
     * 获取标价金额
     * @return mixed|null
     */
    public function getTotalFee()
    {
        return isset($this->data['total_fee']) ? $this->data['total_fee'] : null;
    }

    /**
     * 设置终端IP APP和网页支付提交用户端ip，Native支付填调用微信支付API的机器IP。
     * @param string $ip
     * @return $this
     */
    public function setSpbillCreateIp($ip)
    {
        $this->data['spbill_create_ip'] = $ip;
        return $this;
    }

    /**
     * 获取终端IP
     * @return mixed|null
     */
    public function getSpbillCreateIp()
    {
        return isset($this->data['spbill_create_ip']) ? $this->data['spbill_create_ip'] : null;
    }

    /**
     * 设置交易起始时间 格式为yyyyMMddHHmmss，如2009年12月25日9点10分10秒表示为20091225091010。
     * @param string $timeStart
     * @return $this
     */
    public function setTimeStart($timeStart)
    {
        $this->data['time_start'] = $timeStart;
        return $this;
    }

    /**
     * 获取交易起始时间
     * @return mixed|null
     */
    public function getTimeStart()
    {
        return isset($this->data['time_start']) ? $this->data['time_start'] : null;
    }

    /**
     * 订单失效时间，格式为yyyyMMddHHmmss，如2009年12月27日9点10分10秒表示为20091227091010。
     * @param string $timeExpire
     * @return $this
     */
    public function setTimeExpire($timeExpire)
    {
        $this->data['time_expire'] = $timeExpire;
        return $this;
    }

    /**
     * 获取交易结束时间
     * @return mixed|null
     */
    public function getTimeExpire()
    {
        return isset($this->data['time_expire']) ? $this->data['time_expire'] : null;
    }

    /**
     * 设置商品标记 使用代金券或立减优惠功能时需要的参数
     * @param string $goodsTag
     * @return $this
     */
    public function setGoodsTag($goodsTag)
    {
        $this->data['goods_tag'] = $goodsTag;
        return $this;
    }

    /**
     * 获取商品标记
     * @return mixed|null
     */
    public function getGoodsTag()
    {
        return isset($this->data['goods_tag']) ? $this->data['goods_tag'] : null;
    }

    /**
     * 异步接收微信支付结果通知的回调地址，通知url必须为外网可访问的url，不能携带参数。
     * @param string $notifyUrl
     * @return $this
     */
    public function setNotifyUrl($notifyUrl)
    {
        $this->data['notify_url'] = $notifyUrl;
        return $this;
    }

    /**
     * 获取通知URL
     * @return mixed|null
     */
    public function getNotifyUrl()
    {
        return isset($this->data['notify_url']) ? $this->data['notify_url'] : null;
    }

    /**
     * 设置交易类型
     * @param string $tradeType
     * @return $this
     */
    public function setTradeType($tradeType)
    {
        assert(
            in_array($tradeType, [
                self::TRADE_TYPE_APP,
                self::TRADE_TYPE_JSAPI,
                self::TRADE_TYPE_NATIVE
            ]),
            'trade_type为APP或JSAPI或NATIVE');
        $this->data['trade_type'] = $tradeType;
        return $this;
    }

    /**
     * 获取交易类型
     * @return mixed|null
     */
    public function getTradeType()
    {
        return isset($this->data['trade_type']) ? $this->data['trade_type'] : null;
    }

    /**
     * 设置商品ID 此参数为二维码中包含的商品ID，商户自行定义。
     * @param string $productId
     * @return $this
     */
    public function setProductId($productId)
    {
        $this->data['product_id'] = $productId;
        return $this;
    }

    /**
     * 获取商品ID
     * @return mixed|null
     */
    public function getProductId()
    {
        return isset($this->data['product_id']) ? $this->data['product_id'] : null;
    }

    /**
     * 指定支付方式，no_credit可限制用户不能使用信用卡支付，传入null时取消设置
     * @param string|null $limitPay
     * @return $this
     */
    public function setLimitPay($limitPay = 'no_credit')
    {
        $this->data['limit_pay'] = $limitPay;
        return $this;
    }

    /**
     * 获取支付方式
     * @return mixed|null
     */
    public function getLimitPay()
    {
        return isset($this->data['limit_pay']) ? $this->data['limit_pay'] : null;
    }

    /**
     * 用户标识 trade_type=JSAPI时（即公众号支付），此参数必传，此参数为微信用户在商户对应appid下的唯一标识。
     * @param string $openid
     * @return $this
     */
    public function setOpenid($openid)
    {
        $this->data['openid'] = $openid;
        return $this;
    }

    /**
     * 获取用户OPENID
     * @return mixed|null
     */
    public function getOpenid()
    {
        return isset($this->data['openid']) ? $this->data['openid'] : null;
    }

    /**
     * 获取微信订单号
     * @return mixed|null
     */
    public function getTransactionId()
    {
        return isset($this->data['transaction_id']) ? $this->data['transaction_id'] : null;
    }

    /**
     * 返回data
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }


    /**
     * 使用数组初始化
     * @param Payment $payment
     * @param array $data
     * @return Data
     */
    public static function initWithArray(Payment $payment, array $data)
    {
        $static = new Data($payment);
        $static->data = $data;
        return $static;
    }
}