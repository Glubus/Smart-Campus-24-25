<?php

namespace App\Entity;

use App\Repository\SalleRepository;
use App\Entity\Batiment;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\EtageSalle;

#[ORM\Entity(repositoryClass: SalleRepository::class)]
class Salle
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 1)]
    private ?int $etage = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Batiment $batiment = null;

    #[ORM\OneToMany(targetEntity: Plan::class, mappedBy: 'salle')]
    private Collection $plans;

    public function __construct()
    {
        $this->plans = new ArrayCollection();
    }

    #[ORM\Column(length: 20)]
    private ?string $nom = null;

    public function getId(): ?int
    {
        return $this->id;
    }



    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getEtage(): int
    {
        return $this->etage;
    }

    public function setEtage(int $etage): static
    {
        $this->etage = $etage;

        return $this;
    }

    public function getBatiment(): ?Batiment
    {
        return $this->batiment;
    }

    public function setBatiment(?Batiment $batiment): static
    {
        $this->batiment = $batiment;

        return $this;
    }

    public function getPlan(): ?Plan
    {
        return $this->plan;
    }

    public function setPlan(Plan $plan): static
    {
        // set the owning side of the relation if necessary
        if ($plan->getSalle() !== $this) {
            $plan->setSalle($this);
        }

        $this->plan = $plan;

        return $this;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;
        return $this;
    }

    /**
     * @return Collection<int, Plan>
     */
    public function getPlans(): Collection
    {
        return $this->plans;
    }

    public function addPlan(Plan $plan): static
    {
        if (!$this->plans->contains($plan)) {
            $this->plans->add($plan);
            $plan->setSalle($this);
        }

        return $this;
    }

    public function removePlan(Plan $plan): static
    {
        if ($this->plans->removeElement($plan)) {
            // set the owning side to null (unless already changed)
            if ($plan->getSalle() === $this) {
                $plan->setSalle(null);
            }
        }

        return $this;
    }
}
