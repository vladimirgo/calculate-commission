<?php
namespace App;

use App\Exception\BinLookupException;
use App\Exception\RateProviderException;
use App\Provider\BinProviderInterface;
use App\Provider\ExchangeRateProviderInterface;

class CommissionCalculator
{
    private const EU_COUNTRIES = [
        'AT', 'BE', 'BG', 'CY', 'CZ', 'DE', 'DK', 'EE', 'ES', 'FI', 'FR', 'GR',
        'HR', 'HU', 'IE', 'IT', 'LT', 'LU', 'LV', 'MT', 'NL', 'PO', 'PT', 'RO',
        'SE', 'SI', 'SK',
    ];

    private BinProviderInterface $binProvider;
    private ExchangeRateProviderInterface $rateProvider;

    private const COMMISSION_RATE_EU = 0.01; // 1%
    private const COMMISSION_RATE_NON_EU = 0.02; // 2%
    private const BASE_CURRENCY = 'EUR';

    public function __construct(BinProviderInterface $binProvider, ExchangeRateProviderInterface $rateProvider) {
        $this->binProvider = $binProvider;
        $this->rateProvider = $rateProvider;
    }

    public function calculate(Transaction $transaction): float
    {
        $countryCode = $this->binProvider->getCountryCode($transaction->getBin());
        if ($countryCode === null) {
            throw new BinLookupException("Could not determine country for BIN " . $transaction->getBin());
        }

        $commissionRate = $this->getCommissionRate($countryCode);

        $amount = (float) $transaction->getAmount();
        $currency = $transaction->getCurrency();

        $amountInEur = $this->convertToEur($amount, $currency);

        $commission = $amountInEur * $commissionRate;

        return $this->ceilToCents($commission);
    }

    private function getCommissionRate(string $countryCode): float
    {
        return in_array($countryCode, self::EU_COUNTRIES, true)
            ? self::COMMISSION_RATE_EU
            : self::COMMISSION_RATE_NON_EU;
    }

    private function convertToEur(float $amount, string $currency): float
    {
        if ($currency === self::BASE_CURRENCY) {
            return $amount;
        }

        $rate = $this->rateProvider->getRate($currency);

        if ($rate === null || $rate <= 0) {
            throw new RateProviderException("Could not get valid rate for currency " . $currency);
        }

        return $amount / $rate;
    }

    private function ceilToCents(float $amount): float
    {
        return ceil($amount * 100) / 100;
    }
}
