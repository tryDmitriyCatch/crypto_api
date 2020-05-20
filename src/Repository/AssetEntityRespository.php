<?php

namespace App\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Class AssetEntityRespository
 * @package App\Repository
 */
class AssetEntityRespository extends EntityRepository
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
}
