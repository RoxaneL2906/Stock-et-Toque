<?php

namespace App\Entity;

use App\Repository\AlternativeRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: AlternativeRepository::class)]
#[ORM\Table(name: 'alternative')]
#[ORM\HasLifecycleCallbacks]
class Alternative
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'alternatives', targetEntity: Ingredient::class)]
    #[ORM\JoinColumn(name: 'ingredient_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?Ingredient $ingredient = null;

    #[ORM\ManyToOne(targetEntity: Produit::class)]
    #[ORM\JoinColumn(name: 'produit_substitut_id', referencedColumnName: 'id', nullable: false, onDelete: 'RESTRICT')]
    private ?Produit $produitSubstitut = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(max: 255, maxMessage: 'La note ne doit pas dépasser {{ limit }} caractères.')]
    private ?string $note = null;

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

    public function getIngredient(): ?Ingredient
    {
        return $this->ingredient;
    }

    public function setIngredient(?Ingredient $ingredient): static
    {
        $this->ingredient = $ingredient;
        return $this;
    }

    public function getProduitSubstitut(): ?Produit
    {
        return $this->produitSubstitut;
    }

    public function setProduitSubstitut(?Produit $produitSubstitut): static
    {
        $this->produitSubstitut = $produitSubstitut;
        return $this;
    }

    public function getNote(): ?string
    {
        return $this->note;
    }

    public function setNote(?string $note): static
    {
        $this->note = $note;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }
}
