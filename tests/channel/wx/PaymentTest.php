<?php

/**
 * Created by PhpStorm.
 * User: xialei
 * Date: 2017/3/16
 * Time: 17:07
 */

use payment\channel\wx\Data;
use payment\channel\wx\Payment;

require __DIR__ . '/../../../vendor/autoload.php';

class PaymentTest extends PHPUnit_Framework_TestCase
{
    public function getOrderId()
    {
        $orderId = md5(uniqid());
        file_put_contents(__DIR__ . '/orderId.txt', $orderId);
        return $orderId;
    }

    public function testPrepay()
    {
        $payment = new Payment();
        $data = new Data();
        $orderId = $this->getOrderId();
        $data
            ->setAppId(getenv('WX_APPID'))
            ->setMchId(getenv('WX_MCHID'))
            ->setBody('支付测试')
            ->setOutTradeNo($orderId)
            ->setTotalFee(1)
            ->setTimeStart(date('YmdHis'))
            ->setTimeExpire(date('YmdHis', 3600 * 2 + time()))
            ->setNotifyUrl(getenv('WX_NOTIFY_URL'))
            ->setTradeType(Data::TRADE_TYPE_NATIVE)
            ->setProductId($orderId)
            ->setSpbillCreateIp(getenv('LOCAL_ADDR'))
            ->setNonceStr(strval(rand(100000, 999999)))
            ->sign(getenv('WX_KEY'));
        $response = $payment->prepay($data);
        $this->assertArrayHasKey('return_code', $response);
        $this->assertEquals('SUCCESS',$response['return_code']);
        print_r($response);
    }
}