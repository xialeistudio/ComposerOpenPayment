# OpenPayment
国内主流支付平台库

## 单元测试
### 微信支付
在命令行配置环境变量：
+ WX_APPID 微信支付APPID
+ WX_MCHID 微信支付商户号
+ WX_KEY 微信支付商户密钥
+ LOCAL_ADDR 服务器IP
+ WX_NOTIFY_URL 异步通知URL

***需要使用双向证书的接口，请将微信提供的apiclient_key.pem和apiclient_cert.pem文件放入tests/channel/wx目录下再运行测试***

## 支持平台
+ 微信支付
    + 统一下单
    + 查询订单
    + 关闭订单
    + 申请退款
    + 查询退款
    + 下载对账单
    + 支付结果通知

## 接入文档
### 微信支付
+ 实例化SDK
```php
<?php
        $this->payment = new Payment(
            getenv('WX_MCHID'),
            getenv('WX_KEY'),
            getenv('WX_APPID'),
            getenv('WX_APPSECRET')
        );
```
+ 统一下单
```php
<?php
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
        print_r($response);
```
+ 查询订单
```php
<?php
        $data = new Data($this->payment);
        $data->setOutTradeNo('8c7622456b7838a8aa658568eaa76f71');
        $response = $this->payment->orderQuery($data);
        print_r($response);
```
+ 关闭订单
```php
<?php
        $data = new Data($this->payment);
        $data->setOutTradeNo('9b4987520d1c0c6fd3a6fb1a88e6764b');
        $response = $this->payment->closeOrder($data);
        print_r($response);
```
+ 申请退款
```php
<?php
        $this->payment->setCertFile(__DIR__ . '/apiclient_cert.pem');
        $this->payment->setKeyFile(__DIR__ . '/apiclient_key.pem');
        $data = new Data($this->payment);
        $data
            ->setOutTradeNo('8c7622456b7838a8aa658568eaa76f71')
            ->setTotalFee(1)
            ->setRefundFee(1)
            ->setOutRefundNo($outRefundNo);
        $response = $this->payment->refund($data);
        print_r($response);
```
+ 查询退款
```php
<?php
        $data = new Data($this->payment);
        $data->setOutTradeNo('8c7622456b7838a8aa658568eaa76f71');
        $response = $this->payment->queryRefund($data);
        print_r($response);
```
+ 下载对账单
```php
<?php
        $data = new Data($this->payment);
        $data->setBillDate('20170315');
        $response = $this->payment->downloadBill($data);
        print_r($response);
```
+ 异步通知验证微信签名
```php
<?php
        $data = file_get_contents('php://input');
        $this->payment->validateSign($data);
```
+ 异步通知回复微信
```php
<?php
        $xml = $this->payment->getReply('SUCCESS', 'OK');
        echo $xml;
```