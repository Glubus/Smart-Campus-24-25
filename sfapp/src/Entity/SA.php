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


    #[ORM\OneToMany(targetEntity: SALog::class, mappedBy: 'SA')]
    private Collection $sALogs;


    #[ORM\OneToOne(mappedBy: 'SA', cascade: ['persist', 'remove'])]
    private ?DetailPlan $detailPlan = null;


    public function __construct()
    {
        $this->plans = new ArrayCollection();
        $this->sALogs = new ArrayCollection();
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

    public function getDetailPlan(): ?DetailPlan
    {
        return $this->detailPlan;
    }

    public function setDetailPlan(?DetailPlan $detailPlan): static
    {
        $this->detailPlan = $detailPlan;

        return $this;
    }


}
