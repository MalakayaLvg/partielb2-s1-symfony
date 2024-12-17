<?php

namespace App\Entity;

use App\Repository\InvitationRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: InvitationRepository::class)]
class Invitation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['invitation:detail','event:private'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'invitations')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['invitation:detail','event:private'])]
    private ?Profile $guest = null;

    #[ORM\ManyToOne(inversedBy: 'invitations')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['invitation:detail'])]
    private ?Event $event = null;

    #[ORM\Column(length: 255)]
    #[Groups(['invitation:detail','event:private'])]
    private ?string $status = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getGuest(): ?Profile
    {
        return $this->guest;
    }

    public function setGuest(?Profile $guest): static
    {
        $this->guest = $guest;

        return $this;
    }

    public function getEvent(): ?Event
    {
        return $this->event;
    }

    public function setEvent(?Event $event): static
    {
        $this->event = $event;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }
}
