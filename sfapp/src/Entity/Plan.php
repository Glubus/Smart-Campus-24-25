<?php

namespace App\Entity;

use App\Repository\PlanRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PlanRepository::class)]
class Plan
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToMany(targetEntity: DetailPlan::class, mappedBy: 'plan')]
    private Collection $detailPlans;

    public function __construct()
    {
        $this->detailPlans = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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
}
