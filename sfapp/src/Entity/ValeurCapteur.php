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

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateAjout = null;

    #[ORM\ManyToOne(inversedBy: 'valCapteurs')]
    private ?SA $SA = null;

    #[ORM\ManyToOne(inversedBy: 'valeurCapteurs')]
    private ?Salle $Salle = null;
    #[ORM\Column(type: 'string', length: 255, enumType: TypeCapteur::class)]
    private ?TypeCapteur $type = null;

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

    public function getDateAjout(): ?\DateTimeInterface
    {
        return $this->dateAjout;
    }

    public function setDateAjout(\DateTimeInterface $dateAjout): static
    {
        $this->dateAjout = $dateAjout;

        return $this;
    }

    public function getSA(): ?SA
    {
        return $this->SA;
    }

    public function setSA(?SA $SA): static
    {
        $this->SA = $SA;

        return $this;
    }

    public function getSalle(): ?Salle
    {
        return $this->Salle;
    }

    public function setSalle(?Salle $Salle): static
    {
        $this->Salle = $Salle;

        return $this;
    }

    public function getType(): ?TypeCapteur
    {
        return $this->type;
    }

    public function setType(?TypeCapteur $type): void
    {
        $this->type = $type;
    }
}
