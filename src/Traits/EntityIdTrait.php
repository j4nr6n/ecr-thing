<?php

namespace App\Traits;

use Doctrine\ORM\Mapping as ORM;

/**
 * Adds an ID to entities.
 */
trait EntityIdTrait
{
    /** unique auto-incremented primary key */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    public function getId(): ?int
    {
        return $this->id;
    }
}
