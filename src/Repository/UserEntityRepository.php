<?php

namespace App\Repository;

use App\Entity\UserEntity;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;

/**
 * Class UserEntityRepository
 * @package App\Repository
 */
class UserEntityRepository extends EntityRepository
{
    /**
     * @param $token
     * @return UserEntity|null
     * @throws NonUniqueResultException
     */
    public function findByToken($token): ?UserEntity
    {
        return $this
            ->createQueryBuilder('user')
            ->where('user.token = :token')
            ->setParameter('token', $token)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
