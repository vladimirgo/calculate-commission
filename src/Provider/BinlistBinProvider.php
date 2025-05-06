<?php
namespace App\Provider;

use GuzzleHttp\ClientInterface;

class BinlistBinProvider implements BinProviderInterface {
    private const API_URL = 'https://lookup.binlist.net/';
    private $httpClient;

    public function __construct(ClientInterface $httpClient) {
        $this->httpClient = $httpClient;
    }

    public function getCountryCode(string $bin): ?string {
        try {
            $response = $this->httpClient->request('GET', self::API_URL . $bin);

            if ($response->getStatusCode() !== 200) {
                return null;
            }

            $responseBody = $response->getBody()->getContents();
            if (empty(trim($responseBody))) {
                return null;
            }

            $data = json_decode($responseBody, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return null;
            }

            return $data['country']['alpha2'] ?? null;
        } catch (\Exception $e) {
            return null;
        }
    }
}
