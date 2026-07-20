<?php

namespace App\Entity;

use App\Repository\IngredientRefRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Ingrédient de référence utilisé pour le score de goût et les exclusions par régime.
 * NB : la correspondance produit <-> ingredient_ref se fait par recherche textuelle sur le nom,
 * pas de relation directe en base (voir Service de correspondance).
 */
#[ORM\Entity(repositoryClass: IngredientRefRepository::class)]
#[ORM\Table(name: 'ingredient_ref')]
#[ORM\HasLifecycleCallbacks]
class IngredientRef
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 150)]
    #[Assert\NotBlank(message: 'Le nom est obligatoire.')]
    private ?string $nom = null;

    #[ORM\Column(name: 'created_at', type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $createdAt = null;

    /**
     * Catégories associées (ingredient_categorie).
     *
     * @var Collection<int, Categorie>
     */
    #[ORM\ManyToMany(targetEntity: Categorie::class, inversedBy: 'ingredients')]
    #[ORM\JoinTable(name: 'ingredient_categorie')]
    #[ORM\JoinColumn(name: 'ingredient_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\InverseJoinColumn(name: 'categorie_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Collection $categories;

    /**
     * Régimes qui excluent cet ingrédient (ingredient_regime_alimentaire).
     *
     * @var Collection<int, RegimeAlimentaire>
     */
    #[ORM\ManyToMany(targetEntity: RegimeAlimentaire::class, inversedBy: 'ingredientsExclus')]
    #[ORM\JoinTable(name: 'ingredient_regime_alimentaire')]
    #[ORM\JoinColumn(name: 'ingredient_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\InverseJoinColumn(name: 'regime_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Collection $regimesExclus;

    public function __construct()
    {
        $this->categories = new ArrayCollection();
        $this->regimesExclus = new ArrayCollection();
    }

    #[ORM\PrePersist]
    public function setCreatedAtValue(): void
    {
        if ($this->createdAt === null) {
            $this->createdAt = new \DateTimeImmutable();
        }
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

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * @return Collection<int, Categorie>
     */
    public function getCategories(): Collection
    {
        return $this->categories;
    }

    public function addCategorie(Categorie $categorie): static
    {
        if (!$this->categories->contains($categorie)) {
            $this->categories->add($categorie);
        }
        return $this;
    }

    public function removeCategorie(Categorie $categorie): static
    {
        $this->categories->removeElement($categorie);
        return $this;
    }

    /**
     * @return Collection<int, RegimeAlimentaire>
     */
    public function getRegimesExclus(): Collection
    {
        return $this->regimesExclus;
    }

    public function addRegimeExclu(RegimeAlimentaire $regime): static
    {
        if (!$this->regimesExclus->contains($regime)) {
            $this->regimesExclus->add($regime);
        }
        return $this;
    }

    public function removeRegimeExclu(RegimeAlimentaire $regime): static
    {
        $this->regimesExclus->removeElement($regime);
        return $this;
    }
}
