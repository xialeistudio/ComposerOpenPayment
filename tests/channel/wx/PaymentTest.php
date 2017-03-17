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
}