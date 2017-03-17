#OpenPayment
Payment Library

## 单元测试
### 微信支付
在命令行配置一下环境变量：
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