<?php

namespace App\Services;

use App\Entity\AssetEntity;
use App\Entity\UserEntity;
use App\Exception\AppException;
use App\Services\Traits\DemTrait;
use App\Utils\CountExchangeRateUtils;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

/**
 * Class FindUserDataService
 * @package App\Services
 */
class FindUserDataService
{
    use DemTrait;

    public const USD = 'USD';

    /**
     * @var ExchangeAPIService $exchangeService
     */
    private $exchangeService;

    /**
     * FindUserService constructor.
     * @param EntityManagerInterface $entityManager
     * @param ExchangeAPIService $exchangeService
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        ExchangeAPIService $exchangeService
    )
    {
        $this->dem = $entityManager;
        $this->exchangeService = $exchangeService;
    }

    /**
     * @param string $token
     * @return object|null
     */
    public function getUserByToken($token): ?object
    {
        return $this->getRepository(UserEntity::class)->findByToken($token);
    }

    /**
     * @param string $id
     * @return object|null
     */
    public function getUsersAssetsByToken($id): ?object
    {
        return $this->getRepository(AssetEntity::class)->find($id);
    }

    /**
     * @param string $id
     * @param AssetEntity $assetEntity
     * @return array
     * @throws AppException
     */
    public function returnArrayOfUserAssets($id = null, $assetEntity = null): array
    {
        $assets = [];

        if (!empty($id)) {
            $userAssets = $this->getRepository(AssetEntity::class)->findByUserId($id);
        }

        if ($assetEntity !== null) {
            $userAssets = $assetEntity;
        }

        if ($userAssets !== null) {
            foreach ($userAssets as $asset) {
                $assets[] = [
                    'id' => $asset->getId(),
                    'label' => $asset->getLabel(),
                    'value' => $asset->getValue(),
                    'currency' => $asset->getCurrency(),
                    'value_in_USD' => $this->getCurrentAssetExchangeRateSum($asset),
                ];
            }

        }

        return $assets;
    }

    /**
     * @param $assetEntity
     * @return array
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function returnTotalAssetValuesInUsd($assetEntity): ?array
    {
        $totalValues = $this->getRepository(AssetEntity::class)->getTotalCurrencyCount($assetEntity[0]->getUser()->getId());

        $btcRate = $this->exchangeService->getExchangeRatesForSelectedCurrency(AssetEntity::ASSSET_CURRENCY_KEYS[AssetEntity::ASSSET_CURRENCY_BTC], self::USD);
        $ethRate = $this->exchangeService->getExchangeRatesForSelectedCurrency(AssetEntity::ASSSET_CURRENCY_KEYS[AssetEntity::ASSSET_CURRENCY_ETH], self::USD);
        $iotaRate = $this->exchangeService->getExchangeRatesForSelectedCurrency(AssetEntity::ASSSET_CURRENCY_KEYS[AssetEntity::ASSSET_CURRENCY_IOTA], self::USD);

         return [
            'BTC' => (round(CountExchangeRateUtils::getTotalExchangeSum($btcRate, $totalValues['BTC']), 3)) . ' ' . self::USD,
            'ETH' => (round(CountExchangeRateUtils::getTotalExchangeSum($ethRate, $totalValues['ETH']), 3)) . ' ' . self::USD,
            'IOTA' => (round(CountExchangeRateUtils::getTotalExchangeSum($iotaRate, $totalValues['IOTA']), 3)) . ' ' . self::USD,
        ];
    }

    /**
     * @param $assetEntity
     * @return string
     * @throws AppException
     */
    public function getCurrentAssetExchangeRateSum($assetEntity): string
    {
        try {
            $currency = $this->exchangeService->getExchangeRatesForSelectedCurrency($assetEntity->getCurrencyKeys(), self::USD);
        } catch (ClientExceptionInterface $e) {
            throw new AppException(AppException::EXCEPTION_USER_NOT_FOUND);
        } catch (DecodingExceptionInterface $e) {
            throw new AppException(AppException::EXCEPTION_DECODING_ERROR);
        } catch (RedirectionExceptionInterface $e) {
            throw new AppException(AppException::EXCEPTION_TOO_MANY_REDIRECTS);
        } catch (ServerExceptionInterface $e) {
            throw new AppException(AppException::EXCEPTION_SERVER_ERROR);
        } catch (TransportExceptionInterface $e) {
            throw new AppException(AppException::EXCEPTION_TRANSPORT_ERROR);
        }

        return (round(CountExchangeRateUtils::getTotalExchangeSum($currency, $assetEntity->getValue()), 3)) . ' ' . self::USD;
    }
}
