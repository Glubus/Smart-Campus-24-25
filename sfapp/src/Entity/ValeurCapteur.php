<?php

namespace App\Entity;

use App\Repository\ValeurCapteurRepository;
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
    private ?Capteur $capteur = null;

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

    public function getCapteur(): ?Capteur
    {
        return $this->capteur;
    }

    public function setCapteur(?Capteur $capteur): static
    {
        $this->capteur = $capteur;

        return $this;
    }
}
