<?php

namespace App\Entity;

use App\Repository\SalleRepository;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\BatimentSalle;
use App\Entity\EtageSalle;

#[ORM\Entity(repositoryClass: SalleRepository::class)]
class Salle
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(enumType: BatimentSalle::class)]
    private ?BatimentSalle $batiment = null;

    #[ORM\Column(enumType: EtageSalle::class)]
    private ?EtageSalle $etage = null;

    #[ORM\Column(length: 2, nullable: true)]
    private ?string $numero = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getSalleNom(): string
    {
        $nom = $this->nom = $this->batiment->value . $this->etage->value . str_pad($this->numero, 2, "0", STR_PAD_LEFT);
        return $nom;
    }

    public function getBatiment(): ?BatimentSalle
    {
        return $this->batiment;
    }

    public function setBatiment(BatimentSalle $batiment): static
    {
        $this->batiment = $batiment;

        return $this;
    }

    public function getEtage(): ?EtageSalle
    {
        return $this->etage;
    }

    public function setEtage(?EtageSalle $etage): static
    {
        $this->etage = $etage;

        return $this;
    }

    public function getNumero(): ?string
    {
        return $this->numero;
    }

    public function setNumero(?string $numero): static
    {
        $this->numero = $numero;

        return $this;
    }
}
