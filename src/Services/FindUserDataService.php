<?php

namespace App\Services;

use App\Entity\AssetEntity;
use App\Entity\UserEntity;
use App\Services\Traits\DemTrait;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Class FindUserService
 * @package App\Services
 */
class FindUserDataService
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
     * @return array
     */
    public function returnArrayOfUserAssets($id): array
    {
        $userAssets = $this->getRepository(AssetEntity::class)
            ->findByUserId($id);

        $assets = [];
        foreach ($userAssets as $asset) {
            $assets[] = [
                'id' => $asset->getId(),
                'label' => $asset->getLabel(),
                'value' => $asset->getValue(),
                'currency' => $asset->getCurrency(),
            ];
        }

        return $assets;
    }
}