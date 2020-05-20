<?php

namespace App\Entity;

use App\Entity\Traits\TimestampableEntityTrait;
use App\Entity\Traits\AutoIncrementIdTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="crypto_user",
 *   indexes={
 *     @ORM\Index(name="token", columns={"token"}),
 *     @ORM\Index(name="email", columns={"email"}),
 *   })
 * @ORM\Entity(repositoryClass="App\Repository\UserEntityRepository")
 */
class UserEntity
{
    use AutoIncrementIdTrait;
    use TimestampableEntityTrait;

    /**
     * @var string
     *
     * @ORM\Column(name="token", type="string", length=50, nullable=true)
     */
    private $token;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=100, nullable=true)
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(name="password", type="string", length=50, nullable=true)
     */
    private $password;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=25, nullable=true)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="surname", type="string", length=25, nullable=true)
     */
    private $surname;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\AssetEntity", mappedBy="user")
     */
    private $assets;

    /**
     * Set user token
     *
     * @param string $token
     *
     * @return UserEntity
     */
    public function setToken($token): UserEntity
    {
        $this->token = $token;

        return $this;
    }

    /**
     * Get user token
     *
     * @return string|null
     */
    public function getToken(): ?string
    {
        return $this->token;
    }

    /**
     * Set user email
     *
     * @param string $email
     *
     * @return UserEntity
     */
    public function setEmail($email): UserEntity
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get user email
     *
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * Set user password
     *
     * @param string $password
     *
     * @return UserEntity
     */
    public function setPassword($password): UserEntity
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get user password
     *
     * @return string|null
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * Set user name
     *
     * @param string $name
     *
     * @return UserEntity
     */
    public function setName($name): UserEntity
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get user name
     *
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Set user surname
     *
     * @param string $surname
     *
     * @return UserEntity
     */
    public function setSurname($surname): UserEntity
    {
        $this->surname = $surname;

        return $this;
    }

    /**
     * Get user surname
     *
     * @return string|null
     */
    public function getSurname(): ?string
    {
        return $this->surname;
    }

    /**
     * @return mixed
     */
    public function getAssets()
    {
        return $this->assets;
    }

    /**
     * @param AssetEntity $assets
     * @return UserEntity
     */
    public function setAssets(AssetEntity $assets): UserEntity
    {
        $this->assets = $assets;

        return $this;
    }
}
