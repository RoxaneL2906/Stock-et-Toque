<?php

namespace App\Entity;

use App\Repository\RegimeAlimentaireRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: RegimeAlimentaireRepository::class)]
#[ORM\Table(name: 'regime_alimentaire')]
class RegimeAlimentaire
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: 'Le libellé est obligatoire.')]
    private ?string $libelle = null;

    /**
     * Ingrédients exclus par ce régime (ingredient_regime_alimentaire).
     *
     * @var Collection<int, IngredientRef>
     */
    #[ORM\ManyToMany(targetEntity: IngredientRef::class, mappedBy: 'regimesExclus')]
    private Collection $ingredientsExclus;

    public function __construct()
    {
        $this->ingredientsExclus = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLibelle(): ?string
    {
        return $this->libelle;
    }

    public function setLibelle(string $libelle): static
    {
        $this->libelle = $libelle;
        return $this;
    }

    /**
     * @return Collection<int, IngredientRef>
     */
    public function getIngredientsExclus(): Collection
    {
        return $this->ingredientsExclus;
    }
}
