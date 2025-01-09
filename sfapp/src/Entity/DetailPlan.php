<?php

namespace App\Entity;

use App\Repository\DetailPlanRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DetailPlanRepository::class)]
class DetailPlan
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;


    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateAjout = null;

    #[ORM\ManyToOne(inversedBy: 'detailPlans')]
    private ?Salle $salle = null;


    #[ORM\ManyToOne(inversedBy: 'detailPlans')]
    private ?Plan $plan = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?EtatInstallation $etatSA = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateEnleve = null;

    #[ORM\OneToOne(inversedBy: 'detailPlan', cascade: ['persist'])]
    private ?SA $SA = null;



    public function getId(): ?int
    {
        return $this->id;
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

    public function getPlan(): ?Plan
    {
        return $this->plan;
    }

    public function setPlan(?Plan $plan): static
    {
        $this->plan = $plan;

        return $this;
    }

    public function getEtatSA(): string
    {
        return $this->etatSA->value;
    }

    public function setEtatSA(?EtatInstallation $etatSA): static
    {
        $this->etatSA = $etatSA;

        return $this;
    }

    public function getDateEnleve(): ?\DateTimeInterface
    {
        return $this->dateEnleve;
    }

    public function setDateEnleve(?\DateTimeInterface $dateEnleve): static
    {
        $this->dateEnleve = $dateEnleve;

        return $this;
    }

    public function getSA(): ?SA
    {
        return $this->SA;
    }

    public function setSA(?SA $SA): static
    {
        // unset the owning side of the relation if necessary
        if ($SA === null && $this->SA !== null) {
            $this->SA->setDetailPlan(null);
        }

        // set the owning side of the relation if necessary
        if ($SA !== null && $SA->getDetailPlan() !== $this) {
            $SA->setDetailPlan($this);
        }

        $this->SA = $SA;

        return $this;
    }

}
