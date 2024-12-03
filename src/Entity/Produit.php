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

    #[ORM\ManyToOne(inversedBy: 'lesProduits')]
    private ?Categorie $laCategorie = null;

    #[ORM\OneToOne(inversedBy: 'leProduit', cascade: ['persist', 'remove'])]
    private ?Emplacement $leEmplacement = null;

    #[ORM\OneToOne(inversedBy: 'leProduit', cascade: ['persist', 'remove'])]
    private ?Stock $leStock = null;

    /**
     * @var Collection<int, Detail>
     */
    #[ORM\OneToMany(targetEntity: Detail::class, mappedBy: 'leProduit')]
    private Collection $LesDetails;

    public function __construct()
    {
        $this->LesDetails = new ArrayCollection();
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

    public function getLaCategorie(): ?Categorie
    {
        return $this->laCategorie;
    }

    public function setLaCategorie(?Categorie $laCategorie): static
    {
        $this->laCategorie = $laCategorie;

        return $this;
    }

    public function getLeEmplacement(): ?Emplacement
    {
        return $this->leEmplacement;
    }

    public function setLeEmplacement(?Emplacement $leEmplacement): static
    {
        $this->leEmplacement = $leEmplacement;

        return $this;
    }

    public function getLeStock(): ?Stock
    {
        return $this->leStock;
    }

    public function setLeStock(?Stock $leStock): static
    {
        $this->leStock = $leStock;

        return $this;
    }

    /**
     * @return Collection<int, Detail>
     */
    public function getLesDetails(): Collection
    {
        return $this->LesDetails;
    }

    public function addLesDetail(Detail $lesDetail): static
    {
        if (!$this->LesDetails->contains($lesDetail)) {
            $this->LesDetails->add($lesDetail);
            $lesDetail->setLeProduit($this);
        }

        return $this;
    }

    public function removeLesDetail(Detail $lesDetail): static
    {
        if ($this->LesDetails->removeElement($lesDetail)) {
            // set the owning side to null (unless already changed)
            if ($lesDetail->getLeProduit() === $this) {
                $lesDetail->setLeProduit(null);
            }
        }

        return $this;
    }

}
