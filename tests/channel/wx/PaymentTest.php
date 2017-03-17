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
    /**
     * @var Payment
     */
    private $payment;

    protected function setUp()
    {
        $this->payment = new Payment(
            getenv('WX_MCHID'),
            getenv('WX_KEY'),
            getenv('WX_APPID'),
            getenv('WX_APPSECRET')
        );
    }


    public function getOrderId()
    {
        $orderId = md5(uniqid());
        return $orderId;
    }

    public function getNonceStr()
    {
        return strval(rand(100000, 999999));
    }

    public function testPrepay()
    {
        $data = new Data($this->payment);
        $orderId = $this->getOrderId();
        $data
            ->setBody('支付测试')
            ->setOutTradeNo($orderId)
            ->setTotalFee(1)
            ->setNotifyUrl(getenv('WX_NOTIFY_URL'))
            ->setTradeType(Data::TRADE_TYPE_NATIVE)
            ->setProductId($orderId)
            ->setSpbillCreateIp(getenv('LOCAL_ADDR'));
        $response = $this->payment->prepay($data);
        $this->assertArrayHasKey('return_code', $response);
        $this->assertEquals('SUCCESS', $response['return_code']);
        print_r($response);
    }

    public function testOrderQuery()
    {
        $data = new Data($this->payment);
        $data->setOutTradeNo('8c7622456b7838a8aa658568eaa76f71');
        $response = $this->payment->orderQuery($data);
        $this->assertArrayHasKey('return_code', $response);
        $this->assertEquals('SUCCESS', $response['return_code']);
        print_r($response);
    }

    public function testOrderClose()
    {
        $data = new Data($this->payment);
        $data->setOutTradeNo('9b4987520d1c0c6fd3a6fb1a88e6764b');
        $response = $this->payment->closeOrder($data);
        $this->assertArrayHasKey('return_code', $response);
        $this->assertEquals('SUCCESS', $response['return_code']);
        print_r($response);
    }

    public function testRefund()
    {
        $outRefundNo = $this->getOrderId();
        file_put_contents(__DIR__ . '/out_refund_no.txt', $outRefundNo);
        $this->payment->setCertFile(__DIR__ . '/apiclient_cert.pem');
        $this->payment->setKeyFile(__DIR__ . '/apiclient_key.pem');
        $data = new Data($this->payment);
        $data
            ->setOutTradeNo('8c7622456b7838a8aa658568eaa76f71')
            ->setTotalFee(1)
            ->setRefundFee(1)
            ->setOutRefundNo($outRefundNo);
        $response = $this->payment->refund($data);
        $this->assertArrayHasKey('return_code', $response);
        $this->assertEquals('SUCCESS', $response['return_code']);
        print_r($response);
    }

    public function testQueryRefund()
    {
        $data = new Data($this->payment);
        $data->setOutTradeNo('8c7622456b7838a8aa658568eaa76f71');
        $response = $this->payment->queryRefund($data);
        $this->assertArrayHasKey('return_code', $response);
        $this->assertEquals('SUCCESS', $response['return_code']);
        print_r($response);
    }

    public function testDownloadBill()
    {
        $data = new Data($this->payment);
        $data->setBillDate('20170315');
        $response = $this->payment->downloadBill($data);
        file_put_contents(__DIR__ . '/bill.tar.gz', $response, FILE_BINARY);
    }

    public function testGetReply()
    {
        $xml = $this->payment->getReply('参数错误', 'FAIL');
        $this->assertEquals('<xml><return_code><![CDATA[FAIL]]></return_code><return_msg><![CDATA[参数错误]]></return_msg></xml>', $xml);
    }
}