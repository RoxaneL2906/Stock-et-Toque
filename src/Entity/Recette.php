<?php

namespace App\Entity;

use App\Enum\DifficulteEnum;
use App\Enum\VisibiliteEnum;
use App\Repository\RecetteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\HttpFoundation\File\File;

/**
 * Recette : auteur_id nullable (anonymisation à la suppression du compte),
 * origine_id auto-référence pour gérer le fork.
 * Le champ photo est géré par VichUploaderBundle.
 */
#[ORM\Entity(repositoryClass: RecetteRepository::class)]
#[ORM\Table(name: 'recette')]
#[ORM\HasLifecycleCallbacks]
#[Vich\Uploadable]
class Recette
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'recettes', targetEntity: Utilisateur::class)]
    #[ORM\JoinColumn(name: 'auteur_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?Utilisateur $auteur = null;

    /**
     * Recette d'origine en cas de fork (auto-référence).
     */
    #[ORM\ManyToOne(targetEntity: self::class)]
    #[ORM\JoinColumn(name: 'origine_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?self $origine = null;

    #[ORM\OneToMany(mappedBy: 'origine', targetEntity: self::class)]
    private Collection $forks;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le titre est obligatoire.')]
    private ?string $titre = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(name: 'temps_preparation', nullable: true)]
    #[Assert\PositiveOrZero(message: 'Le temps de préparation doit être positif ou nul.')]
    private ?int $tempsPreparation = null;

    #[ORM\Column(name: 'temps_cuisson', nullable: true)]
    #[Assert\PositiveOrZero(message: 'Le temps de cuisson doit être positif ou nul.')]
    private ?int $tempsCuisson = null;

    #[ORM\Column(name: 'nb_personnes')]
    #[Assert\Positive(message: 'Le nombre de personnes doit être positif.')]
    private ?int $nbPersonnes = null;

    #[ORM\Column(length: 20, nullable: true, enumType: DifficulteEnum::class)]
    private ?DifficulteEnum $difficulte = null;

    #[ORM\Column(name: 'budget_estime', type: Types::DECIMAL, precision: 6, scale: 2, nullable: true)]
    #[Assert\PositiveOrZero(message: 'Le budget estimé doit être positif ou nul.')]
    private ?string $budgetEstime = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $photo = null;

    #[Vich\UploadableField(mapping: 'recettes', fileNameProperty: 'photo')]
    private ?File $imageFile = null;

    #[ORM\Column(name: 'updated_at', type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(length: 20, enumType: VisibiliteEnum::class)]
    private VisibiliteEnum $visibilite = VisibiliteEnum::PRIVEE;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
    private bool $brouillon = false;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
    private bool $anonymisee = false;

    #[ORM\Column(name: 'created_at', type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $createdAt = null;

    /**
     * @var Collection<int, Equipement>
     */
    #[ORM\ManyToMany(targetEntity: Equipement::class)]
    #[ORM\JoinTable(name: 'recette_equipement')]
    #[ORM\JoinColumn(name: 'recette_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\InverseJoinColumn(name: 'equipement_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Collection $equipements;

    #[ORM\OneToMany(mappedBy: 'recette', targetEntity: EtapeRecette::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['ordre' => 'ASC'])]
    private Collection $etapes;

    #[ORM\OneToMany(mappedBy: 'recette', targetEntity: Ingredient::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $ingredients;

    #[ORM\OneToMany(mappedBy: 'recette', targetEntity: Note::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $notes;

    #[ORM\OneToMany(mappedBy: 'recette', targetEntity: Favori::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $favoris;

    #[ORM\OneToMany(mappedBy: 'recette', targetEntity: Commentaire::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $commentaires;

    #[ORM\OneToMany(mappedBy: 'recette', targetEntity: Creneau::class)]
    private Collection $creneaux;

    public function __construct()
    {
        $this->forks = new ArrayCollection();
        $this->equipements = new ArrayCollection();
        $this->etapes = new ArrayCollection();
        $this->ingredients = new ArrayCollection();
        $this->notes = new ArrayCollection();
        $this->favoris = new ArrayCollection();
        $this->commentaires = new ArrayCollection();
        $this->creneaux = new ArrayCollection();
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

    public function getAuteur(): ?Utilisateur
    {
        return $this->auteur;
    }

    public function setAuteur(?Utilisateur $auteur): static
    {
        $this->auteur = $auteur;
        return $this;
    }

    public function getOrigine(): ?self
    {
        return $this->origine;
    }

    public function setOrigine(?self $origine): static
    {
        $this->origine = $origine;
        return $this;
    }

    /**
     * @return Collection<int, self>
     */
    public function getForks(): Collection
    {
        return $this->forks;
    }

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): static
    {
        $this->titre = $titre;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getTempsPreparation(): ?int
    {
        return $this->tempsPreparation;
    }

    public function setTempsPreparation(?int $tempsPreparation): static
    {
        $this->tempsPreparation = $tempsPreparation;
        return $this;
    }

    public function getTempsCuisson(): ?int
    {
        return $this->tempsCuisson;
    }

    public function setTempsCuisson(?int $tempsCuisson): static
    {
        $this->tempsCuisson = $tempsCuisson;
        return $this;
    }

    public function getNbPersonnes(): ?int
    {
        return $this->nbPersonnes;
    }

    public function setNbPersonnes(int $nbPersonnes): static
    {
        $this->nbPersonnes = $nbPersonnes;
        return $this;
    }

    public function getDifficulte(): ?DifficulteEnum
    {
        return $this->difficulte;
    }

    public function setDifficulte(?DifficulteEnum $difficulte): static
    {
        $this->difficulte = $difficulte;
        return $this;
    }

    public function getBudgetEstime(): ?string
    {
        return $this->budgetEstime;
    }

    public function setBudgetEstime(?string $budgetEstime): static
    {
        $this->budgetEstime = $budgetEstime;
        return $this;
    }

    public function getPhoto(): ?string
    {
        return $this->photo;
    }

    public function setPhoto(?string $photo): static
    {
        $this->photo = $photo;
        return $this;
    }

    public function setImageFile(?File $imageFile = null): void
    {
        $this->imageFile = $imageFile;

        if ($imageFile !== null) {
            $this->updatedAt = new \DateTimeImmutable();
        }
    }

    public function getImageFile(): ?File
    {
        return $this->imageFile;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function getVisibilite(): VisibiliteEnum
    {
        return $this->visibilite;
    }

    public function setVisibilite(VisibiliteEnum $visibilite): static
    {
        $this->visibilite = $visibilite;
        return $this;
    }

    public function isBrouillon(): bool
    {
        return $this->brouillon;
    }

    public function setBrouillon(bool $brouillon): static
    {
        $this->brouillon = $brouillon;
        return $this;
    }

    public function isAnonymisee(): bool
    {
        return $this->anonymisee;
    }

    public function setAnonymisee(bool $anonymisee): static
    {
        $this->anonymisee = $anonymisee;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * @return Collection<int, Equipement>
     */
    public function getEquipements(): Collection
    {
        return $this->equipements;
    }

    public function addEquipement(Equipement $equipement): static
    {
        if (!$this->equipements->contains($equipement)) {
            $this->equipements->add($equipement);
        }
        return $this;
    }

    public function removeEquipement(Equipement $equipement): static
    {
        $this->equipements->removeElement($equipement);
        return $this;
    }

    /**
     * @return Collection<int, EtapeRecette>
     */
    public function getEtapes(): Collection
    {
        return $this->etapes;
    }

    /**
     * @return Collection<int, Ingredient>
     */
    public function getIngredients(): Collection
    {
        return $this->ingredients;
    }

    /**
     * @return Collection<int, Note>
     */
    public function getNotes(): Collection
    {
        return $this->notes;
    }

    /**
     * @return Collection<int, Favori>
     */
    public function getFavoris(): Collection
    {
        return $this->favoris;
    }

    /**
     * @return Collection<int, Commentaire>
     */
    public function getCommentaires(): Collection
    {
        return $this->commentaires;
    }

    /**
     * @return Collection<int, Creneau>
     */
    public function getCreneaux(): Collection
    {
        return $this->creneaux;
    }
}