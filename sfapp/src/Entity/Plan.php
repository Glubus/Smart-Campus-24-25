<?php

namespace App\Entity;

use App\Repository\PlanRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use function PHPUnit\Framework\isNull;

#[ORM\Entity(repositoryClass: PlanRepository::class)]
class Plan
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    /**
     * @var Collection<int, DetailPlan>
     */
    #[ORM\OneToMany(targetEntity: DetailPlan::class, mappedBy: 'plan', cascade: ['remove'])]
    private Collection $detailPlans;

    #[ORM\ManyToOne(inversedBy: 'plans')]
    private ?Batiment $Batiment = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $date = null;

    public function getCountSA(): ?int{
        if(is_null($this->detailPlans))
            return 0;
        else
            return $this->detailPlans->count();
    }

    public function getCountSalle(): ?int{
        $arr=[];
        $i=0;
        if(is_null($this->detailPlans))
            return 0;
        foreach ( $this->detailPlans as $plan){

                if (!in_array($plan->getSalle(),$arr)) {
                    $arr[] = $plan->getSalle();
                    $i++;
                }
        }
        return $i;
    }

    public function __construct()
    {
        $this->detailPlans = new ArrayCollection();
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

    /**
     * @return Collection<int, DetailPlan>
     */
    public function getDetailPlans(): Collection
    {
        return $this->detailPlans;
    }

    public function addDetailPlan(DetailPlan $detailPlan): static
    {
        if (!$this->detailPlans->contains($detailPlan)) {
            $this->detailPlans->add($detailPlan);
            $detailPlan->setPlan($this);
        }

        return $this;
    }

    public function removeDetailPlan(DetailPlan $detailPlan): static
    {
        if ($this->detailPlans->removeElement($detailPlan)) {
            // set the owning side to null (unless already changed)
            if ($detailPlan->getPlan() === $this) {
                $detailPlan->setPlan(null);
            }
        }

        return $this;
    }

    public function getBatiment(): ?Batiment
    {
        return $this->Batiment;
    }

    public function setBatiment(?Batiment $Batiment): static
    {
        $this->Batiment = $Batiment;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): static
    {
        $this->date = $date;

        return $this;
    }
}
