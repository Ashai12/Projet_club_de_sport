<?php

namespace App\Entity;

use App\Repository\GroupRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: GroupRepository::class)]
#[ORM\Table(name: '`group`')]
class Group
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(
        groups: ['registration', 'password_update'],
        message: 'le champ doit etre rempli.'
        )]
    #[Assert\Length(
        min: 2,
        max: 255,
        minMessage: 'Au minimum {{ min }} lettres sont attendus.',
        maxMessage: 'Au maximum {{ max }} lettres sont attendus.',
    )]
    #[Assert\NoSuspiciousCharacters]
    private ?string $name = null;

    /**
     * @var Collection<int, Membre>
     */
    #[ORM\ManyToMany(targetEntity: Membre::class, inversedBy: 'groupClub')]
    private Collection $members;

    public function __construct()
    {
        $this->members = new ArrayCollection();
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

    /**
     * @return Collection<int, Membre>
     */
    public function getMembers(): Collection
    {
        return $this->members;
    }

    public function addMember(Membre $member): static
    {
        if (!$this->members->contains($member)) {
            $this->members->add($member);
        }

        return $this;
    }

    public function removeMember(Membre $member): static
    {
        $this->members->removeElement($member);

        return $this;
    }
}
