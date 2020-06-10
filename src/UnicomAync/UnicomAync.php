<?php

namespace Achais\JSTOPay\UnicomAync;

use Achais\JSTOPay\Core\AbstractAPI;
use Achais\JSTOPay\Exceptions\HttpException;
use Achais\JSTOPay\Support\Collection;

class UnicomAync extends AbstractAPI
{
    /**
     * 代理余额查询接口
     * 查询频率建议在30秒以上，用时间间隔可以为30秒、60秒、120秒、210秒这种方式
     *
     * @return Collection|null
     * @throws HttpException
     */
    public function queryBalance()
    {
        $url = $this->getBaseUrl() . '/unicomAync/queryBalance.do';
        $params = [];
        return $this->parseJSON('post', [$url, $params]);
    }

    /**
     * 代理查询订单状态
     *
     * @param string $serialNo 合作方商户系统的流水号,全局唯一,即系统代理方订单号
     * @return Collection|null
     * @throws HttpException
     */
    public function queryBizOrder($serialNo)
    {
        $url = $this->getBaseUrl() . '/unicomAync/queryBizOrder.do';
        $params = [
            'serialNo' => $serialNo,
        ];
        return $this->parseJSON('post', [$url, $params]);
    }

    /**
     * 代理(合作方商户)下单接口，适用于按编码下单的话费、流量、加油卡、视频直充等业务
     * 代理(合作方商户)，需要提供异步通知回调地址，用于接受订单状态的推送
     *
     * @param string $itemId
     * @param int $checkItemFacePrice
     * @param string $uid
     * @param string $serialNo
     * @param string $dtCreate
     * @param int $amt
     * @param int $itemPrice
     * @param string $ext1
     * @param string $ext2
     * @param string $ext3
     * @return Collection|null
     * @throws HttpException
     */
    public function buy($itemId, $checkItemFacePrice, $uid, $serialNo, $dtCreate, $amt = 1, $itemPrice = null,
                        $ext1 = null, $ext2 = null, $ext3 = null)
    {
        $url = $this->getBaseUrl() . '/unicomAync/buy.do';
        $params = [
            'itemId' => $itemId,
            'checkItemFacePrice' => $checkItemFacePrice,
            'uid' => $uid,
            'serialno' => $serialNo,
            'dtCreate' => $dtCreate,
            'amt' => $amt,
            'itemPrice' => $itemPrice,
            'ext1' => $ext1,
            'ext2' => $ext2,
            'ext3' => $ext3,
        ];
        return $this->parseJSON('post', [$url, $params]);
    }
}