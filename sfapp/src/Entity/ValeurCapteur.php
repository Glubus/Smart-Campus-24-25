<?php

namespace App\Entity;

use App\Repository\ValeurCapteurRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ValeurCapteurRepository::class)]
class ValeurCapteur
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?float $valeur = null;

    #[ORM\ManyToOne(inversedBy: 'valeurCapteurs')]
    #[ORM\JoinColumn(nullable: false)]
    private ?SA $sa = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(length: 255)]
    private ?string $description = null;

    #[ORM\Column(type: 'string', length: 255, enumType: TypeCapteur::class)]
    private ?TypeCapteur $nom = null;

    #[ORM\Column(length: 255)]
    private ?string $localisation = null;
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getValeur(): ?float
    {
        return $this->valeur;
    }

    public function setValeur(float $valeur): static
    {
        $this->valeur = $valeur;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): static
    {
        $this->date = $date;

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

    public function getNom(): ?TypeCapteur
    {
        return $this->nom;
    }

    public function setNom(?TypeCapteur $nom): void
    {
        $this->nom = $nom;
    }

    public function getSa(): ?SA
    {
        return $this->sa;
    }

    public function setSa(?SA $sa): void
    {
        $this->sa = $sa;
    }

    public function getLocalisation(): ?string
    {
        return $this->localisation;
    }

    public function setLocalisation(string $localisation): static
    {
        $this->localisation = $localisation;

        return $this;
    }
}
