<?php

namespace App\Services\Traits;

use Doctrine\Common\Proxy\Proxy;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\ORMInvalidArgumentException;
use Doctrine\Persistence\ObjectRepository;
use PDOException;

trait DemTrait
{
    /**
     * @var EntityManagerInterface
     */
    protected $dem;

    /**
     * @param object $object
     * @return bool
     */
    public function contains($object): bool
    {
        return $this->dem->contains($object);
    }

    /**
     * @return $this
     */
    public function flush(): self
    {
        try {
            $this->dem->flush();
        } catch (PDOException $ex) {
            $this->reconnect();
            $this->dem->flush();
        }

        return $this;
    }

    /**
     * @param $entityName
     * @param $entityId
     * @return bool|Proxy|object|null
     * @throws ORMException
     */
    public function getReference($entityName, $entityId)
    {
        return $this->dem->getReference($entityName, $entityId);
    }

    /**
     * @param string $className
     * @return ObjectRepository
     */
    public function getRepository($className): ObjectRepository
    {
        return $this->dem->getRepository($className);
    }

    /**
     * @param object $entity
     * @return object
     */
    public function merge($entity)
    {
        try {
            $merged = $this->dem->merge($entity);
        } catch (ORMInvalidArgumentException $exception) {
            return $entity;
        }

        return $merged;
    }

    /**
     * @param object $entity
     * @param bool   $flush
     * @return $this
     */
    public function persist($entity, $flush = false): self
    {
        if (!$this->dem->getUnitOfWork()->isScheduledForInsert($entity)) {
            try {
                $this->dem->persist($entity);
            } catch (ORMInvalidArgumentException $exception) {
                return $this;
            }
        }

        return $flush ? $this->flush() : $this;
    }

    /**
     * @param object $entity
     * @return $this
     */
    public function refresh($entity): self
    {
        try {
            $this->dem->refresh($entity);
        } catch (ORMInvalidArgumentException $exception) {
            return $this;
        }

        return $this;
    }

    /**
     * @param object $entity
     * @param bool   $flush
     * @return $this
     */
    public function remove($entity, $flush = false): self
    {
        try {
            $this->dem->remove($entity);
        } catch (ORMInvalidArgumentException $exception) {
            return $this;
        }

        return $flush ? $this->flush() : $this;
    }

    /**
     * @param object|array $entity
     * @return $this
     */
    protected function flushEntity($entity): self
    {
        try {
            $this->dem->flush($entity);
        } catch (PDOException $ex) {
            $this->reconnect();
            $this->dem->flush($entity);
        }

        return $this;
    }

    /**
     * @return $this
     */
    private function reconnect(): self
    {
        $connection = $this->dem->getConnection();
        if (false === $connection->ping()) {
            $connection->close();
            $connection->connect();
        }

        return $this;
    }
}
