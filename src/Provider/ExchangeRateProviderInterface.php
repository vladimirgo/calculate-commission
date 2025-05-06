<?php

namespace App\Provider;

interface ExchangeRateProviderInterface {
    public function getRate(string $currency): ?float;
}
