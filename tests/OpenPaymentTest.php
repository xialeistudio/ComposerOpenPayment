<?php

/**
 * Created by PhpStorm.
 * User: xialei
 * Date: 2017/3/16
 * Time: 14:50
 */
use payment\OpenPayment;

require __DIR__ . '/../vendor/autoload.php';

class OpenPaymentTest extends PHPUnit_Framework_TestCase
{
    public function testVersion()
    {
        $this->assertEquals('1.0.0', OpenPayment::VERSION);
    }
}