<?php

namespace Achais\JSTOPay\Foundation\ServiceProviders;

use Achais\JSTOPay\UnicomAync;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class UnicomAyncProvider implements ServiceProviderInterface
{
    public function register(Container $pimple)
    {
        $pimple['unicomAync'] = function ($pimple) {
            return new UnicomAync\UnicomAync($pimple['config']);
        };
    }
}