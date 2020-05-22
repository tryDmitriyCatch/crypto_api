<?php

namespace App\Services;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

/**
 * Class ExchangeAPIService
 * @package AppBundle\Service
 */
class ExchangeAPIService
{
    public const API_KEY = '?apikey=4ACD4922-D788-4B2C-951D-A81ACB231A0A';
    public const URL_EXCHANGE = 'https://rest.coinapi.io/v1/exchangerate/';

    /**
     * @param $currency
     * @param $quoteCurrency
     * @return array
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function getExchangeRatesForSelectedCurrency($currency, $quoteCurrency): array
    {
        $results = [];

        if (!empty($currency)) {
            $results = $this->getResults($currency, $quoteCurrency);
        }

        return $results;
    }

    /**
     * @param $currency
     * @param $quoteCurrency
     * @return array
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    protected function getResults($currency, $quoteCurrency): array
    {
        $response = HttpClient::create()->request(
            'GET', $this->getExchangeURL($currency, $quoteCurrency)
        );

        if ($response->getStatusCode() !== 200) {
            return [];
        }

        return $response->toArray();
    }

    /**
     * @param $currency
     * @param $quoteCurrency
     * @return string
     */
    protected function getExchangeURL($currency, $quoteCurrency): string
    {
        return self::URL_EXCHANGE . $currency . '/' . $quoteCurrency . self::API_KEY;
    }
}
