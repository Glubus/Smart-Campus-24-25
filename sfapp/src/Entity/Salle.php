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
    private Collection $detailPlans;
    #[ORM\Column(length: 20)]
    private ?string $nom = null;

    #[ORM\Column(nullable: true)]
    private ?int $fenetre = null;

    #[ORM\Column(nullable: true)]
    private ?int $radiateur = null;

    /**
     * @var Collection<int, ValeurCapteur>
     */
    #[ORM\OneToMany(targetEntity: ValeurCapteur::class, mappedBy: 'Salle')]
    private Collection $valeurCapteurs;

    /**
     * @var Collection<int, DetailIntervention>
     */
    #[ORM\OneToMany(targetEntity: DetailIntervention::class, mappedBy: 'salle')]
    private Collection $detailInterventions;
    public function __construct()
    {
        $this->plans = new ArrayCollection();
        $this->valeurCapteurs = new ArrayCollection();
        $this->detailInterventions = new ArrayCollection();
    }

    public function getCountSA(): int
    {
        return $this->detailPlans->count();
    }

    public function getOnlySa(): int
    {
        if($this->getCountSA() == 1){

            return $this->detailPlans[0]->getSA()->getId();
        }
        else {
            return -1;
        }
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
    public function getDetailPlans(): Collection
    {
        return $this->detailPlans;
    }

    public function addDetailPlans(DetailPlan $detailPlans): static
    {
        if (!$this->detailPlans->contains($detailPlans)) {
            $this->detailPlans->add($detailPlans);
            $detailPlans->setSalle($this);
        }

        return $this;
    }

    public function removeDetailPlans(DetailPlan $detailPlans): static
    {
        if ($this->plans->removeElement($detailPlans)) {
            // set the owning side to null (unless already changed)
            if ($detailPlans->getSalle() === $this) {
                $detailPlans->setSalle(null);
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

    /**
     * @return Collection<int, ValeurCapteur>
     */
    public function getValeurCapteurs(): Collection
    {
        return $this->valeurCapteurs;
    }

    public function addValeurCapteur(ValeurCapteur $valeurCapteur): static
    {
        if (!$this->valeurCapteurs->contains($valeurCapteur)) {
            $this->valeurCapteurs->add($valeurCapteur);
            $valeurCapteur->setSalle($this);
        }

        return $this;
    }

    public function removeValeurCapteur(ValeurCapteur $valeurCapteur): static
    {
        if ($this->valeurCapteurs->removeElement($valeurCapteur)) {
            // set the owning side to null (unless already changed)
            if ($valeurCapteur->getSalle() === $this) {
                $valeurCapteur->setSalle(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, DetailIntervention>
     */
    public function getDetailInterventions(): Collection
    {
        return $this->detailInterventions;
    }

    public function addDetailIntervention(DetailIntervention $detailIntervention): static
    {
        if (!$this->detailInterventions->contains($detailIntervention)) {
            $this->detailInterventions->add($detailIntervention);
            $detailIntervention->setSalle($this);
        }

        return $this;
    }

    public function removeDetailIntervention(DetailIntervention $detailIntervention): static
    {
        if ($this->detailInterventions->removeElement($detailIntervention)) {
            // set the owning side to null (unless already changed)
            if ($detailIntervention->getSalle() === $this) {
                $detailIntervention->setSalle(null);
            }
        }

        return $this;
    }
}
