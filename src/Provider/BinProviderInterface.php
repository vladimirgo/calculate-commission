<?php

namespace App\Provider;

interface BinProviderInterface {
    public function getCountryCode(string $bin): ?string;
}
