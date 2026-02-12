<?php

namespace App\Entity;

use App\Repository\MembreRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Validator\Constraints as Assert;




#[ORM\Entity(repositoryClass: MembreRepository::class)]
#[UniqueEntity('email')] // Vérification avant l'envoi en BD
class Membre implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(
        groups: ['registration', 'password_update'],
        message: 'le champ doit etre rempli.'
        )]
    #[Assert\Length(
        min: 2,
        max: 60,
        minMessage: 'Au minimum {{ min }} lettres sont attendus.',
        maxMessage: 'Au maximum {{ max }} lettres sont attendus.',
    )]
    #[Assert\NoSuspiciousCharacters]
    #[Assert\Regex(
        pattern: '/^[a-zA-ZÀ-ÿ\- ]+$/',
        message: 'Le prénom ne doit contenir que des lettres.'
    )]
    private ?string $name = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(
        groups: ['registration', 'password_update'],
        message: 'le champ doit etre rempli'
        )]
    #[Assert\Length(
        min: 2,
        max: 60,
        minMessage: 'Au minimum {{ min }} lettres sont attendus.',
        maxMessage: 'Au maximum {{ max }} lettres sont attendus.',
    )]
    #[Assert\NoSuspiciousCharacters]
    #[Assert\Regex(
        pattern: '/^[a-zA-ZÀ-ÿ\- ]+$/',
        message: 'Le nom ne doit contenir que des lettres.'
    )]
    private ?string $lastName = null;

    #[ORM\Column(length: 255, unique: true)]
    #[Assert\NotBlank(message: 'le champ doit etre rempli')]
    #[Assert\Email(
        groups: ['registration', 'password_update'],
        message: "L'email {{ value }} n'est pas un email valide.",
        normalizer: 'trim'
    )]
    #[Assert\NoSuspiciousCharacters]
    private ?string $email = null;

    #[ORM\Column(length: 255)]
    private ?string $password = null;

    #[Assert\NotBlank(
        groups: ['registration', 'password_update'],
        message: 'le champ doit etre rempli'
    )]
    #[Assert\PasswordStrength(
        groups: ['registration', 'password_update'],
        message: 'Le mot de passe est trop faible !'
    )]
    #[Assert\NotCompromisedPassword(
        groups: ['registration', 'password_update'],
        message: "Ce mot de passe a été divulgué."
    )]
    private ?string $plainPassword = null;

    #[ORM\Column(type: 'json')]
    private array $roles = [];

    /**
     * @var Collection<int, Participation>
     */
    #[ORM\OneToMany(targetEntity: Participation::class, mappedBy: 'member')]
    private Collection $participations;

    /**
     * @var Collection<int, Group>
     */
    #[ORM\ManyToMany(targetEntity: Group::class, mappedBy: 'members')]
    private Collection $groupClub;

    public function __construct()
    {
        $this->participations = new ArrayCollection();
        $this->groupClub = new ArrayCollection();
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): static
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }
    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;
        return $this;
    }

    public function getFullName(): string
    {
        return $this->name . ' ' . $this->lastName;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;

        // rôle minimum garanti
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(?string $plainPassword): self
    {
        $this->plainPassword = $plainPassword;
        return $this;
    }


    public function eraseCredentials(): void {}

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
            $participation->setMember($this);
        }

        return $this;
    }

    public function removeParticipation(Participation $participation): static
    {
        if ($this->participations->removeElement($participation)) {
            // set the owning side to null (unless already changed)
            if ($participation->getMember() === $this) {
                $participation->setMember(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Group>
     */
    public function getGroupClub(): Collection
    {
        return $this->groupClub;
    }

    public function addGroupClub(Group $groupClub): static
    {
        if (!$this->groupClub->contains($groupClub)) {
            $this->groupClub->add($groupClub);
            $groupClub->addMember($this);
        }

        return $this;
    }

    public function removeGroupClub(Group $groupClub): static
    {
        if ($this->groupClub->removeElement($groupClub)) {
            $groupClub->removeMember($this);
        }

        return $this;
    }
}
