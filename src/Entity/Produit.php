<?php

namespace App\Entity;

use App\Repository\ProduitRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProduitRepository::class)]
class Produit
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\Column]
    private ?float $prix = null;

    #[ORM\Column(length: 255)]
    private ?string $stock = null;

    #[ORM\ManyToOne(targetEntity: Categorie::class)]
    #[ORM\JoinColumn(name: 'categorie_id', referencedColumnName: 'id', nullable: true)]
    private ?Categorie $lesCategories = null;

    /**
     * @var Collection<int, Detail>
     */
    #[ORM\ManyToMany(targetEntity: Detail::class, mappedBy: 'lesProduits')]
    private Collection $lesDetails;

    public function __construct()
    {
        $this->lesDetails = new ArrayCollection();
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

    public function getPrix(): ?float
    {
        return $this->prix;
    }

    public function setPrix(float $prix): static
    {
        $this->prix = $prix;

        return $this;
    }

    public function getStock(): ?string
    {
        return $this->stock;
    }

    public function setStock(string $stock): static
    {
        $this->stock = $stock;

        return $this;
    }

    public function getLesCategories(): ?Categorie
    {
        return $this->lesCategories;
    }

    public function setLesCategories(?Categorie $lesCategories): static
    {
        $this->lesCategories = $lesCategories;

        return $this;
    }

    /**
     * @return Collection<int, Detail>
     */
    public function getLesDetails(): Collection
    {
        return $this->lesDetails;
    }

    public function addLesDetails(Detail $lesDetails): static
    {
        if (!$this->lesDetails->contains($lesDetails)) {
            $this->lesDetails->add($lesDetails);
            $lesDetails->addLesProduit($this);
        }

        return $this;
    }

    public function removeLesDetails(Detail $lesDetails): static
    {
        if ($this->lesDetails->removeElement($lesDetails)) {
            $lesDetails->removeLesProduit($this);
        }

        return $this;
    }
}
