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
     * @param string $id
     * @return object|null
     */
    public function getUserById($id): ?object
    {
        return $this->getRepository(UserEntity::class)->find($id);
    }
}