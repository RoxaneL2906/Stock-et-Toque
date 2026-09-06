<?php

namespace App\Entity;

use App\Enum\SmileyEnum;
use App\Repository\PreferenceGoutRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PreferenceGoutRepository::class)]
#[ORM\Table(name: 'preference_gout')]
#[ORM\UniqueConstraint(name: 'uniq_utilisateur_ingredient', columns: ['utilisateur_id', 'ingredient_ref_id'])]
#[ORM\HasLifecycleCallbacks]
class PreferenceGout
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'preferencesGout', targetEntity: Utilisateur::class)]
    #[ORM\JoinColumn(name: 'utilisateur_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?Utilisateur $utilisateur = null;

    #[ORM\ManyToOne(targetEntity: IngredientRef::class)]
    #[ORM\JoinColumn(name: 'ingredient_ref_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?IngredientRef $ingredientRef = null;

    #[Assert\NotNull(message: 'Le smiley est obligatoire.')]
    #[ORM\Column(length: 20, enumType: SmileyEnum::class)]
    private ?SmileyEnum $smiley = null;

    #[ORM\Column(name: 'created_at', type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $createdAt = null;

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

    public function getUtilisateur(): ?Utilisateur
    {
        return $this->utilisateur;
    }

    public function setUtilisateur(?Utilisateur $utilisateur): static
    {
        $this->utilisateur = $utilisateur;
        return $this;
    }

    public function getIngredientRef(): ?IngredientRef
    {
        return $this->ingredientRef;
    }

    public function setIngredientRef(?IngredientRef $ingredientRef): static
    {
        $this->ingredientRef = $ingredientRef;
        return $this;
    }

    public function getSmiley(): ?SmileyEnum
    {
        return $this->smiley;
    }

    public function setSmiley(SmileyEnum $smiley): static
    {
        $this->smiley = $smiley;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }
}
