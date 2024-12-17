<?php

namespace App\Entity;

use App\Repository\SuggestionRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: SuggestionRepository::class)]
class Suggestion
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['suggestion:detail'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['suggestion:detail'])]
    private ?string $title = null;

    #[ORM\Column(length: 255)]
    #[Groups(['suggestion:detail'])]
    private ?string $description = null;

    #[ORM\Column]
    #[Groups(['suggestion:detail'])]
    private ?bool $supported = null;

    #[ORM\ManyToOne(inversedBy: 'suggestions')]
    #[Groups(['suggestion:detail'])]
    private ?Profile $profile = null;

    #[ORM\ManyToOne(inversedBy: 'suggestions')]
    #[Groups(['suggestion:detail'])]
    private ?Contributions $eventContributions = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function isSupported(): ?bool
    {
        return $this->supported;
    }

    public function setSupported(bool $supported): static
    {
        $this->supported = $supported;

        return $this;
    }

    public function getProfile(): ?Profile
    {
        return $this->profile;
    }

    public function setProfile(?Profile $profile): static
    {
        $this->profile = $profile;

        return $this;
    }

    public function getEventContributions(): ?Contributions
    {
        return $this->eventContributions;
    }

    public function setEventContributions(?Contributions $eventContributions): static
    {
        $this->eventContributions = $eventContributions;

        return $this;
    }
}
