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


    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $dateAjout = null;

    #[ORM\OneToOne(inversedBy: 'plan', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: true)]
    private ?SA $sa = null;

    #[ORM\OneToOne(inversedBy: 'plan', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: true)]
    private ?Salle $salle = null;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSA(): ?SA
    {
        return $this->sa;
    }

    public function setSA(SA $SA): static
    {
        $this->sa = $SA;

        return $this;
    }

    public function getSalle(): ?Salle
    {
        return $this->salle;
    }

    public function setSalle(Salle $Salle): static
    {
        $this->salle = $Salle;

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
