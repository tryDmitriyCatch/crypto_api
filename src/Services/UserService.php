<?php

namespace App\Services;

use App\Entity\UserEntity;
use App\Services\Traits\ContainerTrait;
use App\Services\Traits\DemTrait;
use App\Services\Traits\SessionTrait;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * Class UserService
 * @package AppBundle\Service
 */
class UserService
{
    use DemTrait;
    use SessionTrait;
    use ContainerTrait;

    /**
     * @param EntityManagerInterface $dem
     * @param Session $session
     * @param ContainerInterface $container
     */
    public function __construct(
        EntityManagerInterface $dem,
        Session $session,
        ContainerInterface $container
    )
    {
        $this->dem = $dem;
        $this->session = $session;
        $this->container = $container;
    }

    /**
     * @return UserEntity
     */
    public function getUser(): UserEntity
    {
        return $this->session->get('userEntity');
    }
}
