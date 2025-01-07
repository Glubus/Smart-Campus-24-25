<?php

namespace App\Entity;

use App\Repository\DetailInterventionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DetailInterventionRepository::class)]
class DetailIntervention
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'detailInterventions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Utilisateur $technicien = null;

    #[ORM\ManyToOne(inversedBy: 'detailInterventions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Salle $salle = null;
    #[ORM\Column(type: 'string', length: 255, enumType: EtatIntervention::class)]
    private ?EtatIntervention $etat = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $dateAjout = null;

    #[ORM\Column(length: 255)]
    private ?string $description = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTechnicien(): ?Utilisateur
    {
        return $this->technicien;
    }

    public function setTechnicien(?Utilisateur $technicien): static
    {
        $this->technicien = $technicien;

        return $this;
    }

    public function getSalle(): ?Salle
    {
        return $this->salle;
    }

    public function setSalle(?Salle $salle): static
    {
        $this->salle = $salle;

        return $this;
    }

    public function getEtat(): ?EtatIntervention
    {
        return $this->etat;
    }

    public function setEtat(?EtatIntervention $etat): void
    {
        $this->etat = $etat;
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }
}
