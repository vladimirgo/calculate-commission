<?php
namespace App\Provider;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;

class ExchangeRatesApiRateProvider implements ExchangeRateProviderInterface {
    private const API_URL = 'https://api.exchangeratesapi.io/latest';
    private const BASE_CURRENCY = 'EUR';

    private $httpClient;
    private $apiKey;
    private $ratesCache = [];

    public function __construct(ClientInterface $httpClient, string $apiKey) {
        $this->httpClient = $httpClient;
        $this->apiKey = $apiKey;
    }

    public function getRate(string $currency): ?float {
        if ($currency === self::BASE_CURRENCY) {
            return 1.0;
        }

        if (isset($this->ratesCache[$currency])) {
            return $this->ratesCache[$currency];
        }

        if (empty($this->ratesCache)) {
            if (!$this->fetchRates()) {
                return null;
            }
        }

        return isset($this->ratesCache[$currency]) ? (float)$this->ratesCache[$currency] : null;
    }

    private function fetchRates(): bool {
        try {
            $response = $this->httpClient->request('GET', self::API_URL, [
                'query' => [
                    'access_key' => $this->apiKey
                ],
            ]);

            if ($response->getStatusCode() !== 200) {
                return false;
            }

            $responseBody = $response->getBody()->getContents();
            $data = json_decode($responseBody, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return false;
            }

            if (!isset($data['rates']) || !is_array($data['rates'])) {
                return false;
            }

            $this->ratesCache = $data['rates'];
            $this->ratesCache[self::BASE_CURRENCY] = 1.0;

            return true;
        } catch (\Exception $e) {
            // Log the exception if needed
            return false;
        }
    }
}
