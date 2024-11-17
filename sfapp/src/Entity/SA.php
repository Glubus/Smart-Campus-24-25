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


    #[ORM\OneToMany(mappedBy: 'SA', targetEntity: Capteur::class, cascade: ['persist', 'remove'])]
    private Collection $capteurs;

    #[ORM\OneToOne(mappedBy: 'sa', cascade: ['persist', 'remove'])]
    private ?Plan $plan = null;

    public function __construct()
    {
        $this->capteurs = new ArrayCollection();
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

    public function getPlan(): ?Plan
    {
        return $this->plan;
    }

    public function setPlan(Plan $plan): static
    {
        // set the owning side of the relation if necessary
        if ($plan->getSa() !== $this) {
            $plan->setSa($this);
        }

        $this->plan = $plan;

        return $this;
    }
}
