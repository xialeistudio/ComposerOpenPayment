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
    // 退款资金来源
    const REFUND_SOURCE_UNSETTLED_FUNDS = 'REFUND_SOURCE_UNSETTLED_FUNDS';//未结算资金，默认使用
    const REFUND_SOURCE_RECHARGE_FUNDS = 'REFUND_SOURCE_RECHARGE_FUNDS';// 可用余额退款
    // 账单类型
    const BILL_TYPE_ALL = 'ALL';
    const BILL_TYPE_REFUND = 'REFUND';
    const RECHARGE_REFUND = 'RECHARGE_REFUND';
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
     * @param bool $autoSet
     * @return string
     */
    public function sign($autoSet = true)
    {
        if ($autoSet) {
            // 清空当前签名
            $this->setSign(null);
        }
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
        if ($autoSet) {
            // 设置签名
            $this->setSign($sign);
        }
        return $sign;
    }

    /**
     * 设置签名类型 默认为MD5，支持HMAC-SHA256和MD5。
     * @param string $signType
     * @return $this
     */
    public function setSignType($signType)
    {
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
     * 设置Data
     * @param array $data
     * @return $this
     */
    public function setData(array $data)
    {
        $this->data = $data;
        return $this;
    }

    /**
     * 设置商户退款单号
     * @param string $outRefundNo
     * @return $this
     */
    public function setOutRefundNo($outRefundNo)
    {
        $this->data['out_refund_no'] = $outRefundNo;
        return $this;
    }

    /**
     * 获取商户退款单号
     * @return mixed|null
     */
    public function getOutRefundNo()
    {
        return isset($this->data['out_refund_no']) ? $this->data['out_refund_no'] : null;
    }

    /**
     * 设置退款金额
     * @param integer $fee
     * @return $this
     */
    public function setRefundFee($fee)
    {
        assert(is_int($fee) && $fee > 0, 'refund_fee为正整数');
        $this->data['refund_fee'] = $fee;
        return $this;
    }

    /**
     * 获取退款金额
     * @return mixed|null
     */
    public function getRefundFee()
    {
        return isset($this->data['refund_fee']) ? $this->data['refund_fee'] : null;
    }

    /**
     * 设置退款币种
     * @param string $type
     * @return $this
     */
    public function setRefundFeeType($type)
    {
        $this->data['refund_fee_type'] = $type;
        return $this;
    }

    /**
     * 获取退款类型
     * @return mixed|null
     */
    public function getRefundFeeType()
    {
        return isset($this->data['refund_fee_type']) ? $this->data['refund_fee_type'] : null;
    }

    /**
     * 操作员 默认商户ID
     * @param string $userId
     * @return $this
     */
    public function setOpUserId($userId = null)
    {
        // 默认商户号
        if ($userId === null) {
            $userId = $this->payment->getMchId();
        }
        $this->data['op_user_id'] = $userId;
        return $this;
    }

    /**
     * 获取操作员
     * @return mixed|null
     */
    public function getOpUserId()
    {
        return isset($this->data['op_user_id']) ? $this->data['op_user_id'] : null;
    }

    /**
     * 设置退款金来源
     * @param string $account
     * @return $this
     */
    public function setRefundAccount($account)
    {
        $this->data['refund_account'] = $account;
        return $this;
    }

    /**
     * 获取退款账户
     * @return mixed|null
     */
    public function getRefundAccount()
    {
        return isset($this->data['refund_account']) ? $this->data['refund_account'] : null;
    }

    /**
     * 设置微信退款单号
     * @param string $id
     * @return $this
     */
    public function setRefundId($id)
    {
        $this->data['refund_id'] = $id;
        return $this;
    }

    /**
     * 获取微信退款单号
     * @return mixed|null
     */
    public function getRefundId()
    {
        return isset($this->data['refund_id']) ? $this->data['refund_id'] : null;
    }

    /**
     * 设置对账单日期 格式:20140603
     * @param string $billDate
     * @return $this
     */
    public function setBillDate($billDate)
    {
        $this->data['bill_date'] = $billDate;
        return $this;
    }

    /**
     * 获取对账单日期
     * @return mixed|null
     */
    public function getBillDate()
    {
        return isset($this->data['bill_date']) ? $this->data['bill_date'] : null;
    }

    /**
     * 账单类型
     * @param string $type
     * @return $this
     */
    public function setBillType($type)
    {
        $this->data['bill_type'] = $type;
        return $this;
    }

    /**
     * 获取账单类型
     * @return mixed|null
     */
    public function getBillType()
    {
        return isset($this->data['bill_type']) ? $this->data['bill_type'] : null;
    }

    /**
     * 压缩账单 非必传参数，固定值：GZIP，返回格式为.gzip的压缩包账单。不传则默认为数据流形式。
     * @param string $type
     * @return $this
     */
    public function setTarType($type = 'GZIP')
    {
        $this->data['tar_type'] = $type;
        return $this;
    }

    /**
     * 获取压缩账单类型
     * @return mixed|null
     */
    public function getTarType()
    {
        return isset($this->data['tar_type']) ? $this->data['tar_type'] : null;
    }

    /**
     * JSAPI设置timestamp
     * @param int $timestamp
     * @return $this
     */
    public function setTimestamp($timestamp)
    {
        $this->data['timeStamp'] = $timestamp;
        return $this;
    }

    /**
     * JSAPI获取timestamp
     * @return mixed|null
     */
    public function getTimestamp()
    {
        return isset($this->data['timeStamp']) ? $this->data['timeStamp'] : null;
    }

    /**
     * 设置订单详情扩展字符串
     * @param string $package
     * @return $this
     */
    public function setPackage($package)
    {
        $this->data['package'] = $package;
        return $this;
    }

    /**
     * 订单详情扩展字符串
     * @return mixed|null
     */
    public function getPackage()
    {
        return isset($this->data['package']) ? $this->data['package'] : null;
    }

    /**
     * JSAPI设置paySign
     * @param string $sign
     * @return $this
     */
    public function setPaySign($sign)
    {
        $this->data['paySign'] = $sign;
        return $this;
    }

    /**
     * JSAPI获取paySign
     * @return mixed|null
     */
    public function getPaySign()
    {
        return isset($this->data['paySign']) ? $this->data['paySign'] : null;
    }

    /**
     * 红包：商户订单号
     * @param integer $number 10位不重复数字
     * @return $this
     */
    public function setMchBillNo($number)
    {
        $this->data['mch_billno'] = $this->payment->getMchId() . date('Ymd') . $number;
        return $this;
    }

    /**
     * 红包：获取商户订单号
     * @return mixed|null
     */
    public function getMchBillNo()
    {
        return isset($this->data['mch_billno']) ? $this->data['mch_billno'] : null;
    }

    /**
     * 红包：设置公众号APPID
     * @param string $appid
     * @return $this
     */
    public function setWxAppId($appid)
    {
        $this->data['wxappid'] = $appid;
        return $this;
    }

    /**
     * 红包：获取微信公众号APPID
     * @return mixed|null
     */
    public function getWxAppId()
    {
        return isset($this->data['wxappid']) ? $this->data['wxappid'] : null;
    }

    /**
     * 红包：设置发送者名称
     * @param string $name
     * @return $this
     */
    public function setSendName($name)
    {
        $this->data['send_name'] = $name;
        return $this;
    }

    /**
     * 红包：获取发送者名称
     * @return mixed|null
     */
    public function getSendName()
    {
        return isset($this->data['send_name']) ? $this->data['send_name'] : null;
    }

    /**
     * 红包：设置红包接受者OPENID
     * @param string $openid
     * @return $this
     */
    public function setReOpenid($openid)
    {
        $this->data['re_openid'] = $openid;
        return $this;
    }

    /**
     * 红包：获取红包接受者OPENID
     * @return mixed|null
     */
    public function getReOpenid()
    {
        return isset($this->data['re_openid']) ? $this->data['re_openid'] : null;
    }

    /**
     * 红包：设置付款金额，单位分
     * @param integer $amount
     * @return $this
     */
    public function setTotalAmount($amount)
    {
        $this->data['total_amount'] = $amount;
        return $this;
    }

    /**
     * 红包：获取付款金额
     * @return mixed|null
     */
    public function getTotalAmount()
    {
        return isset($this->data['total_amount']) ? $this->data['total_amount'] : null;
    }

    /**
     * 红包：设置发放总人数
     * @param int $num
     * @return $this
     */
    public function setTotalNum($num)
    {
        $this->data['total_num'] = $num;
        return $this;
    }

    /**
     * 红包：获取发放总人数
     * @return mixed|null
     */
    public function getTotalNum()
    {
        return isset($this->data['total_num']) ? $this->data['total_num'] : null;
    }

    /**
     * 红包：设置红包祝福语
     * @param string $wishing
     * @return $this
     */
    public function setWishing($wishing)
    {
        $this->data['wishing'] = $wishing;
        return $this;
    }

    /**
     * 红包：获取红包祝福语
     * @return mixed|null
     */
    public function getWishing()
    {
        return isset($this->data['wishing']) ? $this->data['wishing'] : null;
    }

    /**
     * 红包：设置调用接口的IP地址
     * @param string $ip
     * @return $this
     */
    public function setClientIp($ip)
    {
        $this->data['client_ip'] = $ip;
        return $this;
    }

    /**
     * 红包：获取调用接口的IP地址
     * @return mixed|null
     */
    public function getClientIp()
    {
        return isset($this->data['client_ip']) ? $this->data['client_ip'] : null;
    }

    /**
     * 红包：活动名称
     * @param string $actName
     * @return $this
     */
    public function setActName($actName)
    {
        $this->data['act_name'] = $actName;
        return $this;
    }

    /**
     * 红包：获取活动名称
     * @return mixed|null
     */
    public function getActName()
    {
        return isset($this->data['act_name']) ? $this->data['act_name'] : null;
    }

    /**
     * 红包：设置备注
     * @param string $remark
     * @return $this
     */
    public function setRemark($remark)
    {
        $this->data['remark'] = $remark;
        return $this;
    }

    /**
     * 红包：获取备注
     * @return mixed|null
     */
    public function getRemark()
    {
        return isset($this->data['remark']) ? $this->data['remark'] : null;
    }

    /**
     * 红包：设置场景ID
     * @param string $id
     * @inheritdoc https://pay.weixin.qq.com/wiki/doc/api/tools/cash_coupon.php?chapter=13_4&index=3
     * @return $this
     */
    public function setSceneId($id)
    {
        $this->data['scene_id'] = $id;
        return $this;
    }

    /**
     * 红包：获取场景ID
     * @return mixed|null
     */
    public function getSceneId()
    {
        return isset($this->data['scene_id']) ? $this->data['scene_id'] : null;
    }

    /**
     * 红包：设置设置活动信息
     * @param string $info
     * @return $this
     */
    public function setRiskInfo($info)
    {
        $this->data['risk_info'] = $info;
        return $this;
    }

    /**
     * 红包：获取活动信息
     * @return mixed|null
     */
    public function getRiskInfo()
    {
        return isset($this->data['risk_info']) ? $this->data['risk_info'] : null;
    }

    /**
     * 设置资金授权商户号
     * @param string $id
     * @return $this
     */
    public function setConsumeMchId($id)
    {
        $this->data['consume_mch_id'] = $id;
        return $this;
    }

    /**
     * 获取资金授权商户号
     * @return mixed|null
     */
    public function getConsumeMchId()
    {
        return isset($this->data['consume_mch_id']) ? $this->data['consume_mch_id'] : null;
    }

    /**
     * 裂变红包：红包金额设置方式
     * @param string $type
     * @return $this
     */
    public function setAmtType($type = 'ALL_RAND')
    {
        $this->data['amt_type'] = $type;
        return $this;
    }

    /**
     * 裂变红包：获取红包金额设置方式
     * @return mixed|null
     */
    public function getAmtType()
    {
        return isset($this->data['amt_type']) ? $this->data['amt_type'] : null;
    }
}