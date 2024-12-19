<?php

namespace App\Entity;

use App\Repository\BatimentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BatimentRepository::class)]
class Batiment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 10)]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    private ?string $adresse = null;

    #[ORM\Column]
    private ?int $nbEtages = null;




    /**
     * @var Collection<int, Salle>
     */
    #[ORM\OneToMany(targetEntity: Salle::class, mappedBy: 'batiment', cascade: ['persist', 'remove'])]
    private Collection $salles;

    #[ORM\Column(type: "json", nullable: true)]
    private array $etages = [];

    #[ORM\ManyToOne(inversedBy: 'batiments')]
    private ?Plan $plan = null;

    public function __construct()
    {
        $this->salles = new ArrayCollection();
        $this->etages = []; // Initialize as an empty array
    }
    /**
     * Get the list of etages.
     *
     * @return array
     */
    public function renameEtages(int $index, string $newName): self
    {
        $this->etages[$index] = $newName;

        return $this;
    }

    public function getEtages(): array
    {
        return $this->etages;
    }

    /**
     * Add a new etage (name) to the list.
     *
     * @param string $etage
     * @return self
     */
    public function addEtage(string $etage): self
    {
        if (!in_array($etage, $this->etages, true)) {
            $this->etages[] = $etage;
        }

        return $this;
    }

    /**
     * Remove an etage (name) from the list.
     *
     * @param string $etage
     * @return self
     */
    public function removeEtage(string $etage): self
    {
        $this->etages = array_filter($this->etages, fn($e) => $e !== $etage);

        return $this;
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

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(string $adresse): static
    {
        $this->adresse = $adresse;

        return $this;
    }

    public function getNbEtages(): ?int
    {
        return $this->nbEtages;
    }

    public function setNbEtages(int $nbEtages): static
    {
        $this->nbEtages = $nbEtages;
        for($i = 0; $i < $nbEtages; $i++) {
            $this->etages[] = (string)$i;
        }

        return $this;
    }




    /**
     * @return Collection<int, Salle>
     */
    public function getSalles(): Collection
    {
        return $this->salles;
    }

    public function addSalle(Salle $salle): static
    {
        if (!$this->salles->contains($salle)) {
            $this->salles->add($salle);
            $salle->setBatiment($this);
        }

        return $this;
    }

    public function removeSalle(Salle $salle): static
    {
        if ($this->salles->removeElement($salle)) {
            if ($salle->getBatiment() === $this) {
                $salle->setBatiment(null);
            }
        }

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
