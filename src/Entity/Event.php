<?php

namespace App\Entity;

use App\Repository\EventRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: EventRepository::class)]
class Event
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['event:detail',"profile:get",'invitation:detail','event:private','suggestion:detail'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['event:detail',"profile:get",'event:private'])]
    private ?string $place = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['event:detail',"profile:get",'event:private'])]
    private ?string $description = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\GreaterThanOrEqual("today", message: "Event starting date cant be in the past")]
    #[Groups(['event:detail','event:private'])]
    private ?\DateTimeInterface $startDate = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\GreaterThan(propertyPath: "startDate", message: "Event ending date cant be before event starting date")]
    #[Groups(['event:detail','event:private'])]
    private ?\DateTimeInterface $endDate = null;

    #[ORM\Column]
    #[Groups(['event:detail','event:private'])]
    private ?bool $status = null;

    #[ORM\Column]
    #[Groups(['event:detail','event:private'])]
    private ?bool $placeType = null;

    #[ORM\ManyToOne(inversedBy: 'event')]
    #[Groups(['event:detail'])]
    private ?Profile $organizer = null;

    /**
     * @var Collection<int, Profile>
     */
    #[ORM\ManyToMany(targetEntity: Profile::class, inversedBy: 'eventJoined')]
    #[Groups(['event:detail',])]
    private Collection $participants;

    /**
     * @var Collection<int, Invitation>
     */
    #[ORM\OneToMany(targetEntity: Invitation::class, mappedBy: 'event', orphanRemoval: true)]
    #[Groups(['event:private',])]
    private Collection $invitations;

    #[ORM\Column]
    #[Groups(['event:detail','event:private'])]
    private ?bool $canceled = null;

    #[ORM\OneToOne(mappedBy: 'event', cascade: ['persist', 'remove'])]
    #[Groups(['event:detail',])]
    private ?Contributions $contributions = null;

    public function __construct()
    {
        $this->participants = new ArrayCollection();
        $this->invitations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPlace(): ?string
    {
        return $this->place;
    }

    public function setPlace(string $place): static
    {
        $this->place = $place;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTimeInterface $startDate): static
    {
        $now = new \DateTime();
        if ($startDate < $now) {
            throw new \InvalidArgumentException('Event starting date cant be in the past');
        }
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(\DateTimeInterface $endDate): static
    {
        if ($endDate <= $this->startDate) {
            throw new \InvalidArgumentException('Event ending date cant be before event starting date');
        }
        $this->endDate = $endDate;

        return $this;
    }

    public function isStatus(): ?bool
    {
        return $this->status;
    }

    public function setStatus(bool $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function isPlaceType(): ?bool
    {
        return $this->placeType;
    }

    public function setPlaceType(bool $placeType): static
    {
        $this->placeType = $placeType;

        return $this;
    }

    public function getOrganizer(): ?Profile
    {
        return $this->organizer;
    }

    public function setOrganizer(?Profile $organizer): static
    {
        $this->organizer = $organizer;

        return $this;
    }

    /**
     * @return Collection<int, Profile>
     */
    public function getParticipants(): Collection
    {
        return $this->participants;
    }

    public function addParticipant(Profile $participant): static
    {
        if (!$this->participants->contains($participant)) {
            $this->participants->add($participant);
        }

        return $this;
    }

    public function removeParticipant(Profile $participant): static
    {
        $this->participants->removeElement($participant);

        return $this;
    }

    /**
     * @return Collection<int, Invitation>
     */
    public function getInvitations(): Collection
    {
        return $this->invitations;
    }

    public function addInvitation(Invitation $invitation): static
    {
        if (!$this->invitations->contains($invitation)) {
            $this->invitations->add($invitation);
            $invitation->setEvent($this);
        }

        return $this;
    }

    public function removeInvitation(Invitation $invitation): static
    {
        if ($this->invitations->removeElement($invitation)) {
            // set the owning side to null (unless already changed)
            if ($invitation->getEvent() === $this) {
                $invitation->setEvent(null);
            }
        }

        return $this;
    }

    public function isCanceled(): ?bool
    {
        return $this->canceled;
    }

    public function setCanceled(bool $canceled): static
    {
        $this->canceled = $canceled;

        return $this;
    }

    public function getContributions(): ?Contributions
    {
        return $this->contributions;
    }

    public function setContributions(Contributions $contributions): static
    {
        // set the owning side of the relation if necessary
        if ($contributions->getEvent() !== $this) {
            $contributions->setEvent($this);
        }

        $this->contributions = $contributions;

        return $this;
    }
}
