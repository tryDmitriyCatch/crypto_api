<?php

namespace App\Entity\Traits;

use DateTime;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Class TimestampableEntityTrait.
 */
trait TimestampableEntityTrait
{
    /**
     * @var DateTime
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime", name="created_at", options={"default": "CURRENT_TIMESTAMP"}, nullable=true)
     */
    protected $createdAt;

    /**
     * @var DateTime
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(type="datetime", name="updated_at", options={"default": "CURRENT_TIMESTAMP"}, nullable=true)
     */
    protected $updatedAt;

    /**
     * @return DateTimeImmutable
     * Returns createdAt.
     */
    public function getCreatedAt(): DateTimeImmutable
    {
        return DateTimeImmutable::createFromMutable($this->createdAt);
    }

    /**
     * @param DateTimeImmutable $createdAt
     * @return $this
     */
    public function setCreatedAt(DateTimeImmutable $createdAt): self
    {
        $this->createdAt = DateTime::createFromImmutable($createdAt);

        return $this;
    }

    /**
     * @return DateTimeImmutable
     */
    public function getUpdatedAt()
    {
        return DateTimeImmutable::createFromMutable($this->updatedAt);
    }

    /**
     * @param DateTimeImmutable $updatedAt
     * @return $this
     */
    public function setUpdatedAt(DateTimeImmutable $updatedAt)
    {
        $this->updatedAt = DateTime::createFromImmutable($updatedAt);

        return $this;
    }
}