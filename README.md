<h1 align="center"> JSTOPay </h1>

<p align="center"> 聚速通-话费、流量、加油卡、视频直充 SDK for PHP.</p>

<p align="center"> 让你忽略第三方的 Http 请求规则、加密方式和加密实现, 只需关注自己的业务代码</p>

## 安装

```shell
$ composer require achais/jstopay:dev-master -vvv
```

## 使用
配置信息和实例化
```php
use Achais\JSTOPay\JSTOPay;

$config = [
    'debug' => true, // 开启调试

    'key' => 'ea0c81*************cc65c1', // 替换成你自己的
    'user_id' => '8***0', // 替换成你自己的

    // 日志
    'log' => [
        'level' => 'debug',
        'permission' => 0777,
        'file' => '/tmp/jstopay-' . date('Y-m-d') . '.log', // 日志文件, 你可以自定义
    ],
];

$jstopay = new JSTOPay($config);
```
> 不管使用什么功能, 配置信息和实例化 JSTOPay 是必须的

#### 查询余额
```php
use Achais\JSTOPay\JSTOPay;

$config = []; // 配置信息如上
$jstopay = new JSTOPay($config);

$ret = $jstopay->unicomAync->queryBalance();

结果:
Collection {#294 ▼
  #items: array:5 [▼
    "status" => "success"
    "desc" => "交易成功"
    "code" => "00"
    "balance" => 48830
    "success" => true
  ]
}
```
> 返回的是一个 Collection 对象, 或者为 null

## 文档

更多功能介绍请看源码或 Wiki.

## 贡献

You can contribute in one of three ways:

1. File bug reports using the [issue tracker](https://github.com/achais/lianlianpay/issues).
2. Answer questions or fix bugs on the [issue tracker](https://github.com/achais/lianlianpay/issues).
3. Contribute new features or update the wiki.

_The code contribution process is not very formal. You just need to make sure that you follow the PSR-0, PSR-1, and PSR-2 coding guidelines. Any new code contributions must be accompanied by unit tests where applicable._

## License

MIT