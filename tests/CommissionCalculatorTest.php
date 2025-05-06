<?php
namespace Tests;

use App\CommissionCalculator;
use App\Exception\BinLookupException;
use App\Exception\RateProviderException;
use App\Provider\BinProviderInterface;
use App\Provider\ExchangeRateProviderInterface;
use App\Transaction;
use PHPUnit\Framework\TestCase;

class CommissionCalculatorTest extends TestCase
{
    private $binProviderMock;
    private $rateProviderMock;
    private $calculator;

    protected function setUp(): void
    {
        $this->binProviderMock = $this->createMock(BinProviderInterface::class);
        $this->rateProviderMock = $this->createMock(ExchangeRateProviderInterface::class);
        $this->calculator = new CommissionCalculator($this->binProviderMock, $this->rateProviderMock);
    }

    public function testCalculateCommissionForEUCountry()
    {
        $transaction = new Transaction('45717360', '100.00', 'EUR');

        $this->binProviderMock->method('getCountryCode')->willReturn('DE');
        $this->rateProviderMock->method('getRate')->willReturn(1.0);

        $result = $this->calculator->calculate($transaction);

        $this->assertEquals(1.00, $result);
    }

    public function testCalculateCommissionForNonEUCountry()
    {
        $transaction = new Transaction('516793', '50.00', 'USD');

        $this->binProviderMock->method('getCountryCode')->willReturn('US');
        $this->rateProviderMock->method('getRate')->willReturn(1.1);

        $result = $this->calculator->calculate($transaction);

        $this->assertEquals(0.91, $result);
    }

    public function testCalculateThrowsExceptionOnBinLookupFailure()
    {
        $transaction = new Transaction('999999', '100.00', 'EUR');

        $this->binProviderMock->method('getCountryCode')->willReturn(null);

        $this->expectException(BinLookupException::class);
        $this->calculator->calculate($transaction);
    }

    public function testCalculateThrowsExceptionOnRateLookupFailure()
    {
        $transaction = new Transaction('516793', '50.00', 'USD');

        $this->binProviderMock->method('getCountryCode')->willReturn('US');
        $this->rateProviderMock->method('getRate')->willReturn(null);

        $this->expectException(RateProviderException::class);
        $this->calculator->calculate($transaction);
    }

    public function testCeilingToCents()
    {
        $transaction = new Transaction('516793', '50.00', 'USD');

        $this->binProviderMock->method('getCountryCode')->willReturn('US');
        $this->rateProviderMock->method('getRate')->willReturn(1.08);

        $result = $this->calculator->calculate($transaction);

        $this->assertEquals(0.93, $result);
    }
}
