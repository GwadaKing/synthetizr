<?php

namespace App\Entity;

use App\Repository\UserUsageRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserUsageRepository::class)]
class UserUsage
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null; // Nouvelle clé primaire simple

    #[ORM\Column(type: Types::BIGINT)]
    private ?int $user_id = null; // Reste l'ID de l'utilisateur

    #[ORM\Column(type: Types::DATE_MUTABLE)] // CHANGEMENT : DATE NORMALE (MUTABLE)
    private ?\DateTime $usage_period = null; // CHANGEMENT : DateTime normal

    #[ORM\Column]
    private int $request_count = 0;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)] // CHANGEMENT : DATETIME MUTABLE
    private ?\DateTime $updated_at = null;

    public function __construct()
    {
        $this->updated_at = new \DateTime(); // CHANGEMENT : DateTime normal
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserId(): ?int
    {
        return $this->user_id;
    }

    public function setUserId(int $user_id): static
    {
        $this->user_id = $user_id;

        return $this;
    }

    // CHANGEMENT: setUsagePeriod prend maintenant un objet DateTime simple
    public function getUsagePeriod(): ?\DateTime
    {
        return $this->usage_period;
    }

    public function setUsagePeriod(\DateTime $usage_period): static
    {
        $this->usage_period = $usage_period;

        return $this;
    }

    public function getRequestCount(): int
    {
        return $this->request_count;
    }

    public function setRequestCount(int $request_count): static
    {
        $this->request_count = $request_count;

        return $this;
    }
    
    public function incrementRequestCount(): self
    {
        $this->request_count++;
        $this->updated_at = new \DateTime(); // CHANGEMENT : DateTime normal
        
        return $this;
    }

    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(\DateTime $updated_at): static
    {
        $this->updated_at = $updated_at;

        return $this;
    }
}