<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Exception\CalculationException;
use App\Input\TransactionFileReader;
use App\Provider\BinlistBinProvider;
use App\Provider\ExchangeRatesApiRateProvider;
use App\CommissionCalculator;
use Dotenv\Dotenv;
use GuzzleHttp\Client;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

$apiKey = $_ENV['ER_API_KEY'] ?? getenv('ER_API_KEY');
if (empty($apiKey)) {
    fwrite(STDERR, "Error: API key (ER_API_KEY) not found in .env file.\n");
    exit(1);
}

if ($argc < 2) {
    echo "Usage: php app.php <input_file>\n";
    exit(1);
}

$inputFile = $argv[1];

try {
    $httpClient = new Client([
        'timeout' => 10.0,
        'http_errors' => false, // Changed to false to prevent exceptions on HTTP errors
    ]);

    $binProvider = new BinlistBinProvider($httpClient);
    $rateProvider = new ExchangeRatesApiRateProvider($httpClient, $apiKey);
    $calculator = new CommissionCalculator($binProvider, $rateProvider);
    $fileReader = new TransactionFileReader();

    $transactions = $fileReader->readTransactions($inputFile);

    foreach ($transactions as $transaction) {
        try {
            $commission = $calculator->calculate($transaction);
            echo number_format($commission, 2, '.', '') . PHP_EOL;
        } catch (CalculationException $e) {
            fwrite(STDERR, sprintf(
                "Error calculating commission for BIN %s: %s\n",
                $transaction->getBin(),
                $e->getMessage()
            ));
        }
    }
} catch (InvalidArgumentException $e) {
    fwrite(STDERR, "Error: " . $e->getMessage() . PHP_EOL);
    exit(1);
} catch (Throwable $e) {
    fwrite(STDERR, "An unexpected error occurred: " . $e->getMessage() . PHP_EOL);
    exit(1);
}
