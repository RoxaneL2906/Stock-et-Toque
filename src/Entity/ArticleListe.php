<?php

namespace App\Entity;

use App\Repository\ArticleListeRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ArticleListeRepository::class)]
#[ORM\Table(name: 'article_liste')]
#[ORM\HasLifecycleCallbacks]
class ArticleListe
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'articles', targetEntity: ListeCourses::class)]
    #[ORM\JoinColumn(name: 'liste_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?ListeCourses $liste = null;

    #[ORM\ManyToOne(inversedBy: 'articlesListe', targetEntity: Produit::class)]
    #[ORM\JoinColumn(name: 'produit_id', referencedColumnName: 'id', nullable: false, onDelete: 'RESTRICT')]
    private ?Produit $produit = null;

    #[ORM\Column]
    #[Assert\Positive(message: 'La quantité doit être positive.')]
    private ?int $quantite = null;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
    private bool $coche = false;

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

    public function getListe(): ?ListeCourses
    {
        return $this->liste;
    }

    public function setListe(?ListeCourses $liste): static
    {
        $this->liste = $liste;
        return $this;
    }

    public function getProduit(): ?Produit
    {
        return $this->produit;
    }

    public function setProduit(?Produit $produit): static
    {
        $this->produit = $produit;
        return $this;
    }

    public function getQuantite(): ?int
    {
        return $this->quantite;
    }

    public function setQuantite(int $quantite): static
    {
        $this->quantite = $quantite;
        return $this;
    }

    public function isCoche(): bool
    {
        return $this->coche;
    }

    public function setCoche(bool $coche): static
    {
        $this->coche = $coche;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }
}
