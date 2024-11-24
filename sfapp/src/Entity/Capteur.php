<?php
namespace App\Entity;

use App\Entity\TypeCapteur;
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
}
