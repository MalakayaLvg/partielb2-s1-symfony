<?php

namespace App\Entity;

use App\Repository\ProfileRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: ProfileRepository::class)]
class Profile
{
    #[ORM\Id]
    #[ORM\Column]
    #[Groups(['event:detail','user:list','profile:get','user:detail'])]
    private ?int $id = null;

    #[ORM\Column(length: 20, nullable: true)]
    #[Groups(['user:detail','event:detail','profile:get','user:list'])]
    private ?string $displayName = null;


    #[ORM\OneToOne(inversedBy: 'profile', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $userProfile = null;

    /**
     * @var Collection<int, Event>
     */
    #[ORM\OneToMany(targetEntity: Event::class, mappedBy: 'organizer')]
    #[Groups(['profile:get'])]
    private Collection $event;

    /**
     * @var Collection<int, Event>
     */
    #[ORM\ManyToMany(targetEntity: Event::class, mappedBy: 'participants')]
    #[Groups(['profile:get'])]
    private Collection $eventJoined;

    public function __construct()
    {
        $this->event = new ArrayCollection();
        $this->eventJoined = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getDisplayName(): ?string
    {
        return $this->displayName;
    }

    public function setDisplayName(string $displayName): static
    {
        $this->displayName = $displayName;

        return $this;
    }

    public function getUserProfile(): ?User
    {
        return $this->userProfile;
    }

    public function setUserProfile(User $userProfile): static
    {
        $this->userProfile = $userProfile;

        return $this;
    }

    /**
     * @return Collection<int, Event>
     */
    public function getEvent(): Collection
    {
        return $this->event;
    }

    public function addEvent(Event $event): static
    {
        if (!$this->event->contains($event)) {
            $this->event->add($event);
            $event->setOrganizer($this);
        }

        return $this;
    }

    public function removeEvent(Event $event): static
    {
        if ($this->event->removeElement($event)) {
            // set the owning side to null (unless already changed)
            if ($event->setOrganizer() === $this) {
                $event->setOrganizer(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Event>
     */
    public function getEventJoined(): Collection
    {
        return $this->eventJoined;
    }

    public function addEventJoined(Event $eventJoined): static
    {
        if (!$this->eventJoined->contains($eventJoined)) {
            $this->eventJoined->add($eventJoined);
            $eventJoined->addParticipant($this);
        }

        return $this;
    }

    public function removeEventJoined(Event $eventJoined): static
    {
        if ($this->eventJoined->removeElement($eventJoined)) {
            $eventJoined->removeParticipant($this);
        }

        return $this;
    }
}
