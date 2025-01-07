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




    #[ORM\ManyToOne(inversedBy: 'batiments')]
    private ?Plan $plan = null;

    /**
     * @var Collection<int, Etage>
     */
    #[ORM\OneToMany(targetEntity: Etage::class, mappedBy: 'batiment', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $etages;

    public function __construct()
    {
        $this->etages = new ArrayCollection();
    }

    public function renameEtage(int $index, string $newName): self
    {
        $etage = $this->etages[$index];
        $etage->setNom($newName);

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
            $etage = new Etage();
            $etage->setBatiment($this);
            $etage->setNiveau($i);
            $etage->setNom((string)$i);
            $this->addEtage($etage);
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

    /**
     * @return Collection<int, Etage>
     */
    public function getEtages(): Collection
    {
        return $this->etages;
    }

    public function addEtage(Etage $etage): static
    {
        if (!$this->etages->contains($etage)) {
            $this->etages->add($etage);
            $etage->setBatiment($this);
        }

        return $this;
    }

    public function removeEtage(Etage $etage): static
    {
        if ($this->etages->removeElement($etage)) {
            // set the owning side to null (unless already changed)
            if ($etage->getBatiment() === $this) {
                $etage->setBatiment(null);
            }
        }

        return $this;
    }

}
