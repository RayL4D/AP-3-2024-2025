<?php

namespace App\Entity;

use App\Repository\EmplacementRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EmplacementRepository::class)]
class Emplacement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $x = null;

    #[ORM\Column]
    private ?int $y = null;

    #[ORM\OneToOne(mappedBy: 'leEmplacement', cascade: ['persist', 'remove'])]
    private ?Produit $leProduit = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getX(): ?int
    {
        return $this->x;
    }

    public function setX(int $x): static
    {
        $this->x = $x;

        return $this;
    }

    public function getY(): ?int
    {
        return $this->y;
    }

    public function setY(int $y): static
    {
        $this->y = $y;

        return $this;
    }

    public function getLeProduit(): ?Produit
    {
        return $this->leProduit;
    }

    public function setLeProduit(?Produit $leProduit): static
    {
        // unset the owning side of the relation if necessary
        if ($leProduit === null && $this->leProduit !== null) {
            $this->leProduit->setLeEmplacement(null);
        }

        // set the owning side of the relation if necessary
        if ($leProduit !== null && $leProduit->getLeEmplacement() !== $this) {
            $leProduit->setLeEmplacement($this);
        }

        $this->leProduit = $leProduit;

        return $this;
    }
}
