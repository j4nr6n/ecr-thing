<?php

namespace App\Traits;

use Doctrine\ORM\Mapping as ORM;

/**
 * Adds created_at and updated_at timestamps to entities.
 *
 * Entities using this must have the HasLifecycleCallbacks annotation.
 */
#[ORM\HasLifecycleCallbacks]
trait TimestampedTrait
{
    #[ORM\Column(type: 'datetime')]
    protected ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: 'datetime')]
    protected ?\DateTimeInterface $updatedAt = null;

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function updateTimestamps(): void
    {
        if ($this->createdAt === null) {
            $this->createdAt = new \DateTime();
        }

        $this->updatedAt = new \DateTime();
    }
}
