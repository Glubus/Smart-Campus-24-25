<?php

namespace App\Entity;

use App\Repository\PlanRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PlanRepository::class)]
class Plan
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?SA $SA = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Salle $Salle = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $dateAjout = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSA(): ?SA
    {
        return $this->SA;
    }

    public function setSA(SA $SA): static
    {
        $this->SA = $SA;

        return $this;
    }

    public function getSalle(): ?Salle
    {
        return $this->Salle;
    }

    public function setSalle(Salle $Salle): static
    {
        $this->Salle = $Salle;

        return $this;
    }

    public function getDateAjout(): ?\DateTimeInterface
    {
        return $this->dateAjout;
    }

    public function setDateAjout(\DateTimeInterface $dateAjout): static
    {
        $this->dateAjout = $dateAjout;

        return $this;
    }
}
