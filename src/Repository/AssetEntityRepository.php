<?php

namespace App\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Class AssetEntityRepository
 * @package App\Repository
 */
class AssetEntityRepository extends EntityRepository
{
    /**
     * @param $userId
     * @return array
     */
    public function findByUserId($userId): array
    {
        return $this
            ->createQueryBuilder('asset')
            ->where('asset.user = :userId')
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param $userId
     * @return array
     */
    public function getTotalCurrencyCount($userId): array
    {
        $query = $this->createQueryBuilder('asset');

        $result = $query
            ->select('SUM(asset.value)')
            ->where('asset.user = :userId')
            ->andWhere('asset.currency IN (:currencies)')
            ->setParameter('userId', $userId)
            ->setParameter('currencies', [1,2,3])
            ->groupBy('asset.currency')
            ->getQuery()
            ->getResult();

        $btcCount = [];
        $ethCount = [];
        $iotaCount = [];

        foreach ($result[0] as $value) {
            $btcCount[] = $value;
        }
        foreach ($result[1] as $value) {
            $ethCount[] = $value;
        }
        foreach ($result[2] as $value) {
            $iotaCount[] = $value;
        }

        return [
            'BTC' => array_sum($btcCount),
            'ETH' => array_sum($ethCount),
            'IOTA' => array_sum($iotaCount),
        ];
    }
}
