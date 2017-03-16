<?php

/**
 * Created by PhpStorm.
 * User: xialei
 * Date: 2017/3/16
 * Time: 15:57
 */
use payment\channel\wx\Data;

require __DIR__ . '/../../../vendor/autoload.php';

class DataTest extends PHPUnit_Framework_TestCase
{
    /**
     * 测试数据赋值
     */
    public function testData()
    {
        $data = new Data();
        $data->setAppId('123');
        $this->assertEquals('123', $data->getAppId());
    }

    /**
     * 测试签名算法
     */
    public function testSign()
    {
        $data = new Data(false);
        $data->setAppId('wxd930ea5d5a258f4f');
        $data->setMchId('10000100');
        $data->setDeviceInfo('1000');
        $data->setBody('test');
        $data->setNonceStr('ibuaiVcKdpRxkhJA');
        $data->sign('192006250b4c09247ec02edce69f6a2d');
        $this->assertEquals('9A0A8659F005D6984697E2CA0A9CF3B7', $data->getSign());
    }
}