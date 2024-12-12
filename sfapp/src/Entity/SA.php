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

    /**
     * @var Collection<int, ValeurCapteur>
     */
    #[ORM\OneToMany(targetEntity: ValeurCapteur::class, mappedBy: 'SA')]
    private Collection $valCapteurs;

    public function __construct()
    {
        $this->capteurs = new ArrayCollection();
        $this->plans = new ArrayCollection();
        $this->sALogs = new ArrayCollection();
        $this->commentaire = new ArrayCollection();
        $this->valCapteurs = new ArrayCollection();
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


    public function getCapteurs(): Collection
    {
        return $this->capteurs;
    }
    public function addCapteur(Capteur $capteur): static
    {
        if (!$this->capteurs->contains($capteur)) {
            $this->capteurs->add($capteur);
            $capteur->setSA($this);
        }

        return $this;
    }

    public function removeCapteur(Capteur $capteur): static
    {
        if ($this->capteurs->removeElement($capteur)) {
            if ($capteur->getSA() === $this) {
                $capteur->setSA(null);
            }
        }

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
            $plan->setSa($this);
        }

        return $this;
    }

    public function removePlan(DetailPlan $plan): static
    {
        if ($this->plans->removeElement($plan)) {
            // set the owning side to null (unless already changed)
            if ($plan->getSa() === $this) {
                $plan->setSa(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, SALog>
     */
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
            // set the owning side to null (unless already changed)
            if ($sALog->getSA() === $this) {
                $sALog->setSA(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ValeurCapteur>
     */
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
            // set the owning side to null (unless already changed)
            if ($valCapteur->getSA() === $this) {
                $valCapteur->setSA(null);
            }
        }

        return $this;
    }


}
