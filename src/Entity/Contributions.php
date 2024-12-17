<?php

namespace App\Entity;

use App\Repository\ContributionsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: ContributionsRepository::class)]
class Contributions
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['event:detail','suggestion:detail'])]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'contributions', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['suggestion:detail'])]
    private ?Event $event = null;

    /**
     * @var Collection<int, Suggestion>
     */
    #[ORM\OneToMany(targetEntity: Suggestion::class, mappedBy: 'eventContributions')]
    private Collection $suggestions;

    public function __construct()
    {
        $this->suggestions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEvent(): ?Event
    {
        return $this->event;
    }

    public function setEvent(Event $event): static
    {
        $this->event = $event;

        return $this;
    }

    /**
     * @return Collection<int, Suggestion>
     */
    public function getSuggestions(): Collection
    {
        return $this->suggestions;
    }

    public function addSuggestion(Suggestion $suggestion): static
    {
        if (!$this->suggestions->contains($suggestion)) {
            $this->suggestions->add($suggestion);
            $suggestion->setEventContributions($this);
        }

        return $this;
    }

    public function removeSuggestion(Suggestion $suggestion): static
    {
        if ($this->suggestions->removeElement($suggestion)) {
            // set the owning side to null (unless already changed)
            if ($suggestion->getEventContributions() === $this) {
                $suggestion->setEventContributions(null);
            }
        }

        return $this;
    }
}
