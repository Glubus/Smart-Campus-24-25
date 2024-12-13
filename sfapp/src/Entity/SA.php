<?php
namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Repository\SARepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SARepository::class)]
class SA
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50, unique: true)]
    private ?string $nom = null;

    #[ORM\OneToMany(targetEntity: DetailPlan::class, mappedBy: 'sa')]
    private Collection $plans;

    #[ORM\OneToMany(targetEntity: SALog::class, mappedBy: 'SA')]
    private Collection $sALogs;

    #[ORM\OneToMany(targetEntity: ValeurCapteur::class, mappedBy: 'SA')]
    private Collection $valCapteurs;

    #[ORM\ManyToOne(targetEntity: Salle::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?Salle $salle = null;

    public function __construct()
    {
        $this->plans = new ArrayCollection();
        $this->sALogs = new ArrayCollection();
        $this->valCapteurs = new ArrayCollection();
        $this->salles = new ArrayCollection();
    }



    public function getId(): ?int
    {
        return $this->id;
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

    public function getPlans(): Collection
    {
        return $this->plans;
    }

    public function addPlan(DetailPlan $plan): static
    {
        if (!$this->plans->contains($plan)) {
            $this->plans->add($plan);
            $plan->setSa($this);
        }

        return $this;
    }

    public function removePlan(DetailPlan $plan): static
    {
        if ($this->plans->removeElement($plan)) {
            if ($plan->getSa() === $this) {
                $plan->setSa(null);
            }
        }

        return $this;
    }

    public function getSALogs(): Collection
    {
        return $this->sALogs;
    }

    public function addSALog(SALog $sALog): static
    {
        if (!$this->sALogs->contains($sALog)) {
            $this->sALogs->add($sALog);
            $sALog->setSA($this);
        }

        return $this;
    }

    public function removeSALog(SALog $sALog): static
    {
        if ($this->sALogs->removeElement($sALog)) {
            if ($sALog->getSA() === $this) {
                $sALog->setSA(null);
            }
        }

        return $this;
    }

    public function getValCapteurs(): Collection
    {
        return $this->valCapteurs;
    }

    public function addValCapteur(ValeurCapteur $valCapteur): static
    {
        if (!$this->valCapteurs->contains($valCapteur)) {
            $this->valCapteurs->add($valCapteur);
            $valCapteur->setSA($this);
        }

        return $this;
    }

    public function removeValCapteur(ValeurCapteur $valCapteur): static
    {
        if ($this->valCapteurs->removeElement($valCapteur)) {
            if ($valCapteur->getSA() === $this) {
                $valCapteur->setSA(null);
            }
        }

        return $this;
    }
    public function getSalles(): Collection
    {
        return $this->salles;
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

    public function removeSalle(Salle $salle): static
    {
        if ($this->salles->removeElement($salle)) {
            if ($salle->getSa() === $this) {
                $salle->setSa(null);
            }
        }

        return $this;
    }
}
