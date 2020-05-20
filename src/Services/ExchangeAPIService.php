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
    public const API_KEY = '&apikey=F77B8140-E153-4CF1-8400-42E24E4F8FF7';
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
        return self::URL_EXCHANGE . $currency . '/' . $quoteCurrency . '?time=' . self::API_KEY;
    }
}
