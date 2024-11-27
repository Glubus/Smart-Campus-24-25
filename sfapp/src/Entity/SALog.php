<?php

namespace App\Entity;

use App\Repository\SALogRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SALogRepository::class)]
class SALog
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $date = null;

    #[ORM\ManyToOne(inversedBy: 'sALogs')]
    #[ORM\JoinColumn(nullable: true)]
    private ?SA $SA = null;

    #[ORM\Column(type: 'string', length: 255, enumType: ActionLog::class)]
    private ?ActionLog $action = null;
    public function getId(): ?int
    {
        return $this->id;
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

    public function getSA(): ?SA
    {
        return $this->SA;
    }

    public function setSA(?SA $SA): static
    {
        $this->SA = $SA;

        return $this;
    }

    public function getAction(): ?ActionLog
    {
        return $this->action;
    }

    public function setAction(?ActionLog $action): static
    {
        $this->action = $action;
        return $this;
    }
}
