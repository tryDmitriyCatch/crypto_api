<?php

namespace App\Entity;

use App\Entity\Traits\TimestampableEntityTrait;
use App\Entity\Traits\AutoIncrementIdTrait;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="crypto_asset")
 * @ORM\Entity(repositoryClass="App\Repository\UserEntityRepository")
 */
class AssetEntity
{
    use AutoIncrementIdTrait;
    use TimestampableEntityTrait;

    public const ASSSET_CURRENCY_BTC = 1;
    public const ASSSET_CURRENCY_ETH = 2;
    public const ASSSET_CURRENCY_IOTA = 3;
    public const ASSSET_CURRENCY_KEYS = [
        self::ASSSET_CURRENCY_BTC => 'BTC',
        self::ASSSET_CURRENCY_ETH => 'ETH',
        self::ASSSET_CURRENCY_IOTA => 'IOTA',
    ];

    /**
     * @var int
     *
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $currency;

    /**
     * @var string
     *
     * @ORM\Column(name="label", type="string", length=25, nullable=true)
     */
    private $label;

    /**
     * @var string
     *
     * @ORM\Column(name="value", type="decimal", precision=18, scale=2, nullable=true, options={"default" = 0.00})
     * @Assert\Positive
     */
    private $value;

    /**
     * @var UserEntity|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\UserEntity", inversedBy="assets")
     */
    private $user;

    /**
     * @return int|null
     */
    public function getCurrency(): ?int
    {
        return $this->currency;
    }

    /**
     * @param int|null $currency
     *
     * @return AssetEntity
     */
    public function setCurrency(?int $currency): self
    {
        $this->currency = $currency;

        return $this;
    }

    /**
     * @return bool
     */
    public function isCurrencyBTC(): bool
    {
        return $this->getCurrency() === self::ASSSET_CURRENCY_BTC;
    }

    /**
     * @return bool
     */
    public function isCurrencyETH(): bool
    {
        return $this->getCurrency() === self::ASSSET_CURRENCY_ETH;
    }

    /**
     * @return bool
     */
    public function isCurrencyIOTA(): bool
    {
        return $this->getCurrency() === self::ASSSET_CURRENCY_IOTA;
    }

    /**
     * @return string|null
     */
    public function getCurrencyKeys(): ?string
    {
        return self::ASSSET_CURRENCY_KEYS[$this->getCurrency()] ?? null;
    }

    /**
     * @return string|null
     */
    public function getLabel(): ?string
    {
        return $this->label;
    }

    /**
     * @param string $label
     */
    public function setLabel(string $label): void
    {
        $this->label = $label;
    }

    /**
     * @return string|null
     */
    public function getValue(): ?string
    {
        return $this->value;
    }

    /**
     * @param string $value
     */
    public function setValue(string $value): void
    {
        $this->value = $value;
    }

    /**
     * @return UserEntity|null
     */
    public function getUser(): ?UserEntity
    {
        return $this->user;
    }

    /**
     * @param UserEntity|null $user
     * @return AssetEntity
     */
    public function setLkuUser(?UserEntity $user): AssetEntity
    {
        $this->user = $user;
        return $this;
    }
}
