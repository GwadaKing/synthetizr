<?php

namespace App\Entity;

use App\Repository\SyntheseRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SyntheseRepository::class)]
class Synthese
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::BIGINT)]
    private ?int $user_id = null;

    #[ORM\Column(type: Types::JSON)] // Modification 1 : Précision du type
    private array $request_params = [];

    #[ORM\Column(type: Types::TEXT)]
    private ?string $result_text = null;

    #[ORM\Column]
    private ?int $api_tokens_used = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)] // Modification 1 : Précision du type
    private ?\DateTimeImmutable $created_at = null;

    public function __construct() // Modification 2 : Ajout du constructeur
    {
        $this->created_at = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserId(): ?int // Modification 3 : Correction du type
    {
        return $this->user_id;
    }

    public function setUserId(int $user_id): static // Modification 3 : Correction du type
    {
        $this->user_id = $user_id;

        return $this;
    }

    public function getRequestParams(): array
    {
        return $this->request_params;
    }

    public function setRequestParams(array $request_params): static
    {
        $this->request_params = $request_params;

        return $this;
    }

    public function getResultText(): ?string
    {
        return $this->result_text;
    }

    public function setResultText(string $result_text): static
    {
        $this->result_text = $result_text;

        return $this;
    }

    public function getApiTokensUsed(): ?int
    {
        return $this->api_tokens_used;
    }

    public function setApiTokensUsed(int $api_tokens_used): static
    {
        $this->api_tokens_used = $api_tokens_used;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeImmutable $created_at): static
    {
        $this->created_at = $created_at;

        return $this;
    }
}