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

    #[ORM\OneToMany(targetEntity: Plan::class, mappedBy: 'sa')]
    private Collection $plans;

    #[ORM\OneToMany(targetEntity: Capteur::class, mappedBy: 'sa', cascade: ['persist'])]
    private Collection $capteurs;

    #[ORM\OneToMany(targetEntity: SALog::class, mappedBy: 'SA')]
    private Collection $sALogs;

    #[ORM\OneToMany(targetEntity: Commentaires::class, mappedBy: 'SA')]
    private Collection $commentaire;

    public function __construct()
    {
        $this->capteurs = new ArrayCollection();
        $this->plans = new ArrayCollection();
        $this->sALogs = new ArrayCollection();
        $this->commentaire = new ArrayCollection();
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

    public function getDateAjout(): ?\DateTime
    {
        return $this->dateAjout;
    }

    public function setDateAjout(\DateTime $dateAjout): static
    {
        $this->dateAjout = $dateAjout;
        return $this;
    }

    public function getCapteurs(): Collection
    {
        return $this->capteurs;
    }
    public function generateCapteurs() {
        // Créer et associer 3 capteurs à cet SA
        for ($i = 1; $i <= 3; $i++) {
            $type = TypeCapteur::cases()[$i - 1];
            $capteur = new Capteur();
            $capteur->setNom('Capteur ' . $i)
                ->setType($type)
                ->setSA($this);
            $this->addCapteur($capteur);

        }
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
            $plan->setSa($this);
        }

        return $this;
    }

    public function removePlan(Plan $plan): static
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
     * @return Collection<int, Commentaires>
     */
    public function getCommentaire(): Collection
    {
        return $this->commentaire;
    }

    public function addCommentaire(Commentaires $commentaire): static
    {
        if (!$this->commentaire->contains($commentaire)) {
            $this->commentaire->add($commentaire);
            $commentaire->setSA($this);
        }

        return $this;
    }

    public function removeCommentaire(Commentaires $commentaire): static
    {
        if ($this->commentaire->removeElement($commentaire)) {
            // set the owning side to null (unless already changed)
            if ($commentaire->getSA() === $this) {
                $commentaire->setSA(null);
            }
        }

        return $this;
    }
}
