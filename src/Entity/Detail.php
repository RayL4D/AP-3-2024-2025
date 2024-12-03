<?php

namespace App\Entity;

use App\Repository\DetailRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DetailRepository::class)]
class Detail
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $quantiteProduit = null;

    /**
     * @var Collection<int, Produit>
     */
    #[ORM\ManyToMany(targetEntity: Produit::class, inversedBy: 'lesDetails')]
    private Collection $lesProduits;

    /**
     * @var Collection<int, Commande>
     */
    #[ORM\ManyToMany(targetEntity: Commande::class, mappedBy: 'lesDetails')]
    private Collection $lesCommandes;

    public function __construct()
    {
        $this->lesProduits = new ArrayCollection();
        $this->lesCommandes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getQuantiteProduit(): ?int
    {
        return $this->quantiteProduit;
    }

    public function setQuantiteProduit(int $quantiteProduit): static
    {
        $this->quantiteProduit = $quantiteProduit;

        return $this;
    }

    /**
     * @return Collection<int, Produit>
     */
    public function getLesProduits(): Collection
    {
        return $this->lesProduits;
    }

    public function addLesProduit(Produit $lesProduit): static
    {
        if (!$this->lesProduits->contains($lesProduit)) {
            $this->lesProduits->add($lesProduit);
        }

        return $this;
    }

    public function removeLesProduit(Produit $lesProduit): static
    {
        $this->lesProduits->removeElement($lesProduit);

        return $this;
    }

    /**
     * @return Collection<int, Commande>
     */
    public function getLesCommandes(): Collection
    {
        return $this->lesCommandes;
    }

    public function addLesCommande(Commande $lesCommande): static
    {
        if (!$this->lesCommandes->contains($lesCommande)) {
            $this->lesCommandes->add($lesCommande);
            $lesCommande->addLesDetail($this);
        }

        return $this;
    }

    public function removeLesCommande(Commande $lesCommande): static
    {
        if ($this->lesCommandes->removeElement($lesCommande)) {
            $lesCommande->removeLesDetail($this);
        }

        return $this;
    }
}
