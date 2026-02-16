<?php

namespace App\Entity;

use App\Repository\TournoiRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: TournoiRepository::class)]
class Tournoi
{
    public const CATEGORIES = ['Poussins', 'Pupille', 'Benjamins', 'Minimes', 'Cadets', 'Juniors', 'Seniors', 'Vétérans'];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    #[Assert\NotBlank]
    #[Assert\GreaterThanOrEqual(
        'today',
        message: "La date du tournoi doit être aujourd'hui ou dans le futur."
    )]
    private ?\DateTimeImmutable $date = null;

    #[ORM\Column(length: 100)]
    #[Assert\Choice(
        choices: Tournoi::CATEGORIES,
        message: 'Choisissez un niveau valide. "Poussins, Pupille, Benjamins, Minimes, Cadets, Juniors, Seniors, Vétérans"',
    )]
    #[Assert\NotBlank]
    #[Assert\NoSuspiciousCharacters]
    private ?string $level = null;

    #[ORM\Column(length: 255)]
    #[Assert\Regex(
        pattern: '/^[\p{L}\d][\p{L}\d\s\'\-.]*$/u',
        message: 'Le nom de rue contient des caractères non autorisés.'
    )]
    #[Assert\NotBlank]
    #[Assert\NoSuspiciousCharacters]
    private ?string $address = null;

    #[ORM\Column(length: 30)]
    #[Assert\Choice(
        choices: ['À venir', 'En cours', 'Terminé'],
        message: 'Choisissez un status valide. "À venir" - "En cours" - "Terminé"',
    )]
    #[Assert\NotBlank]
    #[Assert\NoSuspiciousCharacters]
    private ?string $status = null;

    /**
     * @var Collection<int, Participation>
     */
    #[ORM\OneToMany(targetEntity: Participation::class, mappedBy: 'tournament')]
    private Collection $participations;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank()]
    #[Assert\Regex(
        pattern: '/^[a-zA-ZÀ-ÿ\'\- ]+$/',
        message: "L'intitulé ne doit contenir que des lettres."
    )]
    #[Assert\NoSuspiciousCharacters]
    private ?string $tournamentName = null;

    public function __construct()
    {
        $this->participations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): ?\DateTimeImmutable
    {
        return $this->date;
    }

    public function setDate(\DateTimeImmutable $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getLevel(): ?string
    {
        return $this->level;
    }

    public function setLevel(string $level): static
    {
        $this->level = $level;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): static
    {
        $this->address = $address;

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

    /**
     * @return Collection<int, Participation>
     */
    public function getParticipations(): Collection
    {
        return $this->participations;
    }

    public function addParticipation(Participation $participation): static
    {
        if (!$this->participations->contains($participation)) {
            $this->participations->add($participation);
            $participation->setTournament($this);
        }

        return $this;
    }

    public function removeParticipation(Participation $participation): static
    {
        if ($this->participations->removeElement($participation)) {
            // set the owning side to null (unless already changed)
            if ($participation->getTournament() === $this) {
                $participation->setTournament(null);
            }
        }

        return $this;
    }

    public function getTournamentName(): ?string
    {
        return $this->tournamentName;
    }

    public function setTournamentName(string $tournamentName): static
    {
        $this->tournamentName = $tournamentName;

        return $this;
    }
}
