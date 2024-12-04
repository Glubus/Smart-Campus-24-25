<?php
namespace App\Entity;

use App\Entity\TypeCapteur;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CapteurRepository::class)]
class Capteur
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $nom = null;

    #[ORM\Column(type: 'string', length: 255, enumType: TypeCapteur::class)]
    private ?TypeCapteur $type = null;

    #[ORM\ManyToOne(inversedBy: 'capteurs')]
    private ?SA $sa = null;

    #[ORM\OneToMany(targetEntity: ValeurCapteur::class, mappedBy: 'capteur')]
    private Collection $valeurCapteurs;

    public function __construct()
    {
        $this->valeurCapteurs = new ArrayCollection();
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

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;
        return $this;
    }

    public function getType(): ?TypeCapteur
    {
        return $this->type;
    }

    public function setType(TypeCapteur $type): static
    {
        $this->type = $type;
        return $this;
    }

    public function getSA(): ?SA
    {
        return $this->sa;
    }

    public function setSA(?SA $SA): static
    {
        $this->sa = $SA;
        return $this;
    }

    public function getTypeString(): ?string
    {
        return $this->type?->value;
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
            $valeurCapteur->setCapteur($this);
        }

        return $this;
    }

    public function removeValeurCapteur(ValeurCapteur $valeurCapteur): static
    {
        if ($this->valeurCapteurs->removeElement($valeurCapteur)) {
            // set the owning side to null (unless already changed)
            if ($valeurCapteur->getCapteur() === $this) {
                $valeurCapteur->setCapteur(null);
            }
        }

        return $this;
    }
}
