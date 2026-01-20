<?php

namespace App\Entity;

use App\Repository\MembreRepository;
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
    #[Assert\NotBlank(message: 'le champ {{ label }} doit etre rempli.')]
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
    #[Assert\NotBlank(message: 'le champ {{ label }} doit etre rempli')]
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
    #[Assert\NotBlank(message: 'le champ {{ label }} doit etre rempli')]
    #[Assert\Email(
        message: "L'email {{ value }} n'est pas un email valide.",
        normalizer: 'trim'
    )]
    #[Assert\NoSuspiciousCharacters]
    private ?string $email = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'le champ {{ label }} doit etre rempli')]
    #[Assert\PasswordStrength(
        minScore: 3,
        message: 'le mot de passe est trop faible'
    )]
    #[Assert\NoSuspiciousCharacters]
    #[Assert\NotCompromisedPassword(
        message: "Ce mot de passe a été divulgué, s'il vous plait choisissez en un autre.",
        skipOnError: true
    )]
    private ?string $password = null;

    #[Assert\NotBlank]
    #[Assert\PasswordStrength(
        minScore: 3,
        message: 'Le mot de passe est trop faible'
    )]
    #[Assert\NotCompromisedPassword(
        message: "Ce mot de passe a été divulgué."
    )]
    private ?string $plainPassword = null;

    #[ORM\Column(type: 'json')]
    private array $roles = [];


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
}
