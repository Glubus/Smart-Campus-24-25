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
    private ?SA $sa = null;

    #[ORM\ManyToOne(inversedBy: 'detailPlans')]
    private ?Plan $plan = null;



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

    public function getPlan(): ?Plan
    {
        return $this->plan;
    }

    public function setPlan(?Plan $plan): static
    {
        $this->plan = $plan;

        return $this;
    }

}
