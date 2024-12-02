<?php

namespace App\Entity;

use App\Repository\StockRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: StockRepository::class)]
class Stock
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $quantiteStock = null;

    #[ORM\OneToOne(mappedBy: 'leStock', cascade: ['persist', 'remove'])]
    private ?Produit $leProduit = null;


    public function __construct()
    {

    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getQuantiteStock(): ?int
    {
        return $this->quantiteStock;
    }

    public function setQuantiteStock(int $quantiteStock): static
    {
        $this->quantiteStock = $quantiteStock;

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
            $this->leProduit->setLeStock(null);
        }

        // set the owning side of the relation if necessary
        if ($leProduit !== null && $leProduit->getLeStock() !== $this) {
            $leProduit->setLeStock($this);
        }

        $this->leProduit = $leProduit;

        return $this;
    }
}
