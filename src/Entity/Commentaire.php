<?php

namespace App\Entity;

use App\Repository\CommentaireRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CommentaireRepository::class)]
#[ORM\Table(name: 'commentaire')]
#[ORM\HasLifecycleCallbacks]
class Commentaire
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'commentaires', targetEntity: Recette::class)]
    #[ORM\JoinColumn(name: 'recette_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?Recette $recette = null;

    /**
     * Nullable : passe à null si l'auteur supprime son compte (anonymisation).
     */
    #[ORM\ManyToOne(targetEntity: Utilisateur::class)]
    #[ORM\JoinColumn(name: 'auteur_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?Utilisateur $auteur = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: 'Le commentaire ne peut pas être vide.')]
    #[Assert\Length(max: 500, maxMessage: 'Le commentaire ne doit pas dépasser {{ limit }} caractères.')]
    private ?string $contenu = null;

    /**
     * true si le commentaire a été bloqué par le filtre de modération (liste de mots interdits).
     */
    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
    private bool $modere = false;

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

    public function getRecette(): ?Recette
    {
        return $this->recette;
    }

    public function setRecette(?Recette $recette): static
    {
        $this->recette = $recette;
        return $this;
    }

    public function getAuteur(): ?Utilisateur
    {
        return $this->auteur;
    }

    public function setAuteur(?Utilisateur $auteur): static
    {
        $this->auteur = $auteur;
        return $this;
    }

    public function getContenu(): ?string
    {
        return $this->contenu;
    }

    public function setContenu(string $contenu): static
    {
        $this->contenu = $contenu;
        return $this;
    }

    public function isModere(): bool
    {
        return $this->modere;
    }

    public function setModere(bool $modere): static
    {
        $this->modere = $modere;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }
}
