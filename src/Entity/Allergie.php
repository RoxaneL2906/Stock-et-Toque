<?php

namespace App\Entity;

use App\Repository\AllergieRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: AllergieRepository::class)]
#[ORM\Table(name: 'allergie')]
class Allergie
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: 'Le nom est obligatoire.')]
    private ?string $nom = null;

    /**
     * Catégories exclues des suggestions à cause de cette allergie (allergie_categorie).
     *
     * @var Collection<int, Categorie>
     */
    #[ORM\ManyToMany(targetEntity: Categorie::class, inversedBy: 'allergiesExclusions')]
    #[ORM\JoinTable(name: 'allergie_categorie')]
    #[ORM\JoinColumn(name: 'allergie_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\InverseJoinColumn(name: 'categorie_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Collection $categoriesExclues;

    public function __construct()
    {
        $this->categoriesExclues = new ArrayCollection();
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
     * @return Collection<int, Categorie>
     */
    public function getCategoriesExclues(): Collection
    {
        return $this->categoriesExclues;
    }

    public function addCategorieExclue(Categorie $categorie): static
    {
        if (!$this->categoriesExclues->contains($categorie)) {
            $this->categoriesExclues->add($categorie);
        }
        return $this;
    }

    public function removeCategorieExclue(Categorie $categorie): static
    {
        $this->categoriesExclues->removeElement($categorie);
        return $this;
    }
}
