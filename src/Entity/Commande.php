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
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(length: 255)]
    private ?string $statut = null;

    /**
     * @var Collection<int, Detail>
     */
    #[ORM\OneToMany(targetEntity: Detail::class, mappedBy: 'laCommande')]
    private Collection $lesDetails;

    public function __construct()
    {
        $this->lesDetails = new ArrayCollection();
    }

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

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): static
    {
        $this->statut = $statut;

        return $this;
    }

    /**
     * @return Collection<int, Detail>
     */
    public function getLesDetails(): Collection
    {
        return $this->lesDetails;
    }

    public function addLesDetail(Detail $lesDetail): static
    {
        if (!$this->lesDetails->contains($lesDetail)) {
            $this->lesDetails->add($lesDetail);
            $lesDetail->setLaCommande($this);
        }

        return $this;
    }

    public function removeLesDetail(Detail $lesDetail): static
    {
        if ($this->lesDetails->removeElement($lesDetail)) {
            // set the owning side to null (unless already changed)
            if ($lesDetail->getLaCommande() === $this) {
                $lesDetail->setLaCommande(null);
            }
        }

        return $this;
    }

}
