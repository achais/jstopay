<?php

namespace Achais\JSTOPay\Core;

use Achais\JSTOPay\Exceptions\HttpException;
use Achais\JSTOPay\Exceptions\InternalException;
use Achais\JSTOPay\Foundation\Config;
use Achais\JSTOPay\Support\Arr;
use Achais\JSTOPay\Support\Collection;
use Achais\JSTOPay\Support\Log;
use GuzzleHttp\Middleware;
use Psr\Http\Message\RequestInterface;

abstract class AbstractAPI
{
    /**
     * Http instance.
     *
     * @var Http
     */
    protected $http;

    /**
     * @var Config
     */
    protected $config;

    const GET = 'get';
    const POST = 'post';
    const JSON = 'json';
    const PUT = 'put';
    const DELETE = 'delete';

    /**
     * @var int
     */
    protected static $maxRetries = 0;

    /**
     * Constructor.
     *
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->setConfig($config);
    }

    /**
     * Return the http instance.
     *
     * @return Http
     */
    public function getHttp()
    {
        if (is_null($this->http)) {
            $this->http = new Http();
        }

        if (0 === count($this->http->getMiddlewares())) {
            $this->registerHttpMiddlewares();
        }

        return $this->http;
    }

    /**
     * Set the http instance.
     *
     * @param Http $http
     *
     * @return $this
     */
    public function setHttp(Http $http)
    {
        $this->http = $http;

        return $this;
    }

    /**
     * Return the current config.
     *
     * @return Config
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Set the config.
     *
     * @param Config $config
     *
     * @return $this
     */
    public function setConfig(Config $config)
    {
        $this->config = $config;

        return $this;
    }

    /**
     * @param int $retries
     */
    public static function maxRetries($retries)
    {
        self::$maxRetries = abs($retries);
    }

    public function getBaseUrl()
    {
        $production = $this->getConfig()->get('production');
        if ($production) {
            return 'http://101.201.33.233:8090';
        } else {
            return 'http://101.201.33.233:8090';
        }
    }

    /**
     * Parse JSON from response and check error.
     *
     * @param $method
     * @param array $args
     * @return Collection|null
     * @throws HttpException
     */
    public function parseJSON($method, array $args)
    {
        $http = $this->getHttp();

        $url = $args[0];
        $params = $args[1];

        $userId = $this->getConfig()->get('user_id');
        $params['userId'] = $userId;
        $params = $this->buildSignatureParams($params);

        $contents = $http->parseJSON(call_user_func_array([$http, $method], [$url, $params]));

        if (empty($contents)) {
            return null;
        }

        //$this->checkAndThrow($contents);

        return (new Collection($contents));
    }

    private function buildSignatureParams($params)
    {
        //排除空参数
        $params = $this->filterNull($params);
        //拼接加密内容
        $signRaw = $this->httpBuildKSortQuery($params) . $this->getConfig()->get('key', '');
        $params['sign'] = md5($signRaw);
        return $params;
    }

    private function filterNull($params)
    {
        // 过滤空参数
        $params = Arr::where($params, function ($key, $value) {
            return !is_null($value);
        });
        return $params;
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

    public static function randomString($length = 16)
    {
        $string = '';

        while (($len = strlen($string)) < $length) {
            $size = $length - $len;

            $bytes = random_bytes($size);

            $string .= substr(str_replace(['/', '+', '='], '', base64_encode($bytes)), 0, $size);
        }

        return $string;
    }

    /**
     * Register Guzzle middlewares.
     */
    protected function registerHttpMiddlewares()
    {
        // log
        $this->http->addMiddleware($this->logMiddleware());
        // signature
        $this->http->addMiddleware($this->signatureMiddleware());
    }

    protected function signatureMiddleware()
    {
        return function (callable $handler) {
            return function (RequestInterface $request, array $options) use ($handler) {
                if (!$this->config) {
                    return $handler($request, $options);
                }

                return $handler($request, $options);
            };
        };
    }

    /**
     * Log the request.
     *
     * @return \Closure
     */
    protected function logMiddleware()
    {
        return Middleware::tap(function (RequestInterface $request, $options) {
            Log::debug("Request: {$request->getMethod()} {$request->getUri()} " . json_encode($options));
            Log::debug('Request headers:' . json_encode($request->getHeaders()));
        });
    }

    /**
     * Check the array data errors, and Throw exception when the contents contains error.
     *
     * @param array $contents
     * @throws HttpException
     */
    protected function checkAndThrow(array $contents)
    {
        $successCodes = ['success'];
        if (isset($contents['status']) && !in_array($contents['status'], $successCodes)) {
            if (empty($contents['desc'])) {
                $contents['desc'] = 'Unknown';
            }

            throw new HttpException(json_encode($contents));
        }
    }
}