<?php

namespace App\Services;

use App\Entity\UserEntity;
use App\Services\Traits\DemTrait;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Class FindUserService
 * @package App\Services
 */
class FindUserService
{
    use DemTrait;

    /**
     * FindUserService constructor.
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->dem = $entityManager;
    }

    /**
     * @param string $token
     * @return object|null
     */
    public function getUserByToken($token): ?object
    {
        return $this->getRepository(UserEntity::class)->findByToken($token);
    }
}