<?php

namespace Achais\JSTOPay;

use Achais\JSTOPay\Core\Http;
use Achais\JSTOPay\Support\Arr;
use Achais\JSTOPay\Support\Log;
use Achais\JSTOPay\UnicomAync\UnicomAync;
use Monolog\Handler\HandlerInterface;
use Monolog\Handler\NullHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Pimple\Container;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class Application
 *
 * @property UnicomAync $unicomAync
 *
 * @package Achais\JSTOPay
 */
class JSTOPay extends Container
{
    const CODE_QITA = "0";//其它
    const CODE_PHONE_PAY = "1";//手支
    const CODE_WEB_SITE = "2";//网厅
    const CODE_UNIFIED = "3";//统付
    const CODE_EMPTY = "4";//空充
    const CODE_CARD = "5";//卡密充，卡密的卡号
    const CODE_CARD_PWD = "6";//卡密
    const CODE_JOB_NUM = "7";//工号
    const CODE_BOSS = "8";//boss
    const CODE_WOW = "9";//沃支付
    const CODE_CHARGING = "10";//计费
    const CODE_ELECTRIC_CHANNEL = "11";//电网
    const CODE_PROVICNCE_NETWORK = "12";//省网
    const CODE_FLOW_POOL = "13";//流量池子
    const CODE_NATIONWIDE_TELECOM = "14";//全国电信
    const CODE_NATIONWIDE_UNICOM = "15";//全国联通

    protected $providers = [
        Foundation\ServiceProviders\UnicomAyncProvider::class,
    ];

    public function __construct(array $config = array())
    {
        parent::__construct($config);

        $this['config'] = function () use ($config) {
            return new Foundation\Config($config);
        };

        $this->registerBase();
        $this->registerProviders();
        $this->initializeLogger();

        Http::setDefaultOptions($this['config']->get('guzzle', [
            'timeout' => 5.0,
            'headers' => [
                'Accept' => 'application/json;charset=utf-8'
            ]
        ]));

        $this->logConfiguration($config);
    }

    public function logConfiguration($config)
    {
        $config = new Foundation\Config($config);

        $keys = ['key'];
        foreach ($keys as $key) {
            !$config->has($key) || $config[$key] = '***' . substr($config[$key], -5);
        }

        Log::debug('Current config:', $config->toArray());
    }

    public function addProvider($provider)
    {
        array_push($this->providers, $provider);
        return $this;
    }

    public function setProviders(array $providers)
    {
        $this->providers = [];

        foreach ($providers as $provider) {
            $this->addProvider($provider);
        }
    }

    public function getProviders()
    {
        return $this->providers;
    }

    public function __get($name)
    {
        return $this->offsetGet($name);
    }

    public function __set($name, $value)
    {
        $this->offsetSet($name, $value);
    }

    private function registerProviders()
    {
        foreach ($this->providers as $provider) {
            $this->register(new $provider());
        }
    }

    private function registerBase()
    {
        $this['request'] = function () {
            return Request::createFromGlobals();
        };
    }

    private function initializeLogger()
    {
        if (Log::hasLogger()) {
            return;
        }

        $logger = new Logger('jstopay');

        if (!$this['config']['debug'] || defined('PHPUNIT_RUNNING')) {
            $logger->pushHandler(new NullHandler());
        } elseif ($this['config']['log.handler'] instanceof HandlerInterface) {
            $logger->pushHandler($this['config']['log.handler']);
        } elseif ($logFile = $this['config']['log.file']) {
            try {
                $logger->pushHandler(new StreamHandler(
                        $logFile,
                        $this['config']->get('log.level', Logger::WARNING),
                        true,
                        $this['config']->get('log.permission', null))
                );
            } catch (\Exception $e) {
            }
        }

        Log::setLogger($logger);
    }

    /**
     * @param $method
     * @param $args
     * @return mixed
     * @throws \Exception
     */
    public function __call($method, $args)
    {
        if (is_callable([$this['fundamental.api'], $method])) {
            return call_user_func_array([$this['fundamental.api'], $method], $args);
        }

        throw new \Exception("Call to undefined method {$method}()");
    }

    /**
     * 验证签名
     * @param $params
     * @return bool
     */
    public function verifySignature($params)
    {
        if (!isset($params['sign'])) {
            return false;
        }

        $sign = $params['sign'];
        unset($params['sign'], $params['voucher'], $params['voucherType']);

        $signRaw = $this->httpBuildKSortQuery($params) . $this['config']->get('key', '');

        $result = md5($signRaw) === $sign;

        Log::debug('Verify Signature Result:', compact('result', 'params'));

        return $result;
    }

    private function httpBuildKSortQuery($params)
    {
        // 排序
        ksort($params);
        $str = '';
        foreach ($params as $key => $value) {
            $str .= $value;
        }
        return $str;
    }
}