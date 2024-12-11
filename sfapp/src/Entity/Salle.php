<?php

namespace App\Entity;

use App\Repository\SalleRepository;
use App\Entity\Batiment;
use ContainerWYV09s8\getTranslation_ProviderFactory_NullService;
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

    #[ORM\OneToMany(targetEntity: DetailPlan::class, mappedBy: 'salle')]
    private Collection $plans;
    #[ORM\Column(length: 20)]
    private ?string $nom = null;

    #[ORM\Column(nullable: true)]
    private ?int $fenetre = null;

    #[ORM\Column(nullable: true)]
    private ?int $radiateur = null;
    public function __construct()
    {
        $this->plans = new ArrayCollection();
    }

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

    public function getPlan(): ?DetailPlan
    {
        return $this->plan;
    }

    public function setPlan(DetailPlan $plan): static
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
     * @return Collection<int, DetailPlan>
     */
    public function getPlans(): Collection
    {
        return $this->plans;
    }

    public function addPlan(DetailPlan $plan): static
    {
        if (!$this->plans->contains($plan)) {
            $this->plans->add($plan);
            $plan->setSalle($this);
        }

        return $this;
    }

    public function removePlan(DetailPlan $plan): static
    {
        if ($this->plans->removeElement($plan)) {
            // set the owning side to null (unless already changed)
            if ($plan->getSalle() === $this) {
                $plan->setSalle(null);
            }
        }

        return $this;
    }

    public function getFenetre(): ?int
    {
        return $this->fenetre;
    }

    public function setFenetre(int $fenetre): static
    {
        $this->fenetre = $fenetre;

        return $this;
    }

    public function getRadiateur(): ?int
    {
        return $this->radiateur;
    }

    public function setRadiateur(int $radiateur): static
    {
        $this->radiateur = $radiateur;

        return $this;
    }
}
