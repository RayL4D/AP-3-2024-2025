<?php

namespace App\Entity;

use App\Repository\CommandeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CommandeRepository::class)]
class Commande
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'datetime')]
    private \DateTime $date;

    #[ORM\Column(type: 'string', length: 255)]
    private string $statut;

    #[ORM\OneToMany(targetEntity: Detail::class, mappedBy: 'commande', cascade: ['persist', 'remove'])]
    private Collection $details;

    public function __construct()
    {
        $this->details = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): \DateTime
    {
        return $this->date;
    }

    public function setDate(\DateTime $date): self
    {
        $this->date = $date;
        return $this;
    }

    public function getStatut(): string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): self
    {
        $this->statut = $statut;
        return $this;
    }

    public function getDetails(): Collection
    {
        return $this->details;
    }
public function addDetail(Detail $detail): self
{
    if (!$this->details->contains($detail)) {
        $this->details[] = $detail;
        $detail->setCommande($this);
    }
    return $this;
}

public function removeDetail(Detail $detail): self
{
    $this->details->removeElement($detail);
    return $this;
}



}
