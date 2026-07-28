<?php

namespace App\Entity;

use App\Enum\EmplacementEnum;
use App\Repository\StockRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: StockRepository::class)]
#[ORM\Table(name: 'stock')]
#[ORM\HasLifecycleCallbacks]
class Stock
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'stocks', targetEntity: Utilisateur::class)]
    #[ORM\JoinColumn(name: 'utilisateur_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?Utilisateur $utilisateur = null;

    #[ORM\ManyToOne(inversedBy: 'stocks', targetEntity: Produit::class)]
    #[ORM\JoinColumn(name: 'produit_id', referencedColumnName: 'id', nullable: false, onDelete: 'RESTRICT')]
    private ?Produit $produit = null;

    #[ORM\Column]
    #[Assert\PositiveOrZero(message: 'La quantité doit être positive ou nulle.')]
    private ?int $quantite = null;

    #[ORM\Column(length: 20, enumType: EmplacementEnum::class)]
    #[Assert\NotNull(message: "L'emplacement est obligatoire.")]
    private ?EmplacementEnum $emplacement = null;

    #[ORM\Column(length: 20, enumType: \App\Enum\UniteEnum::class, nullable: true)]
    private ?\App\Enum\UniteEnum $unite = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dlc = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $ddm = null;

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

    public function getEmplacement(): ?EmplacementEnum
    {
        return $this->emplacement;
    }

    public function setEmplacement(EmplacementEnum $emplacement): static
    {
        $this->emplacement = $emplacement;
        return $this;
    }

    public function getUnite(): ?\App\Enum\UniteEnum
    {
        return $this->unite;
    }

    public function setUnite(?\App\Enum\UniteEnum $unite): static
    {
        $this->unite = $unite;
        return $this;
    }

    public function getDlc(): ?\DateTimeInterface
    {
        return $this->dlc;
    }

    public function setDlc(?\DateTimeInterface $dlc): static
    {
        $this->dlc = $dlc;
        return $this;
    }

    public function getDdm(): ?\DateTimeInterface
    {
        return $this->ddm;
    }

    public function setDdm(?\DateTimeInterface $ddm): static
    {
        $this->ddm = $ddm;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }
}
