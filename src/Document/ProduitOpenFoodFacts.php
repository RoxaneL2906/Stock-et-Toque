<?php

namespace App\Document;

use App\DocumentRepository\ProduitOpenFoodFactsRepository;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

#[MongoDB\Document(collection: 'produits_openfoodfacts', repositoryClass: ProduitOpenFoodFactsRepository::class)]
class ProduitOpenFoodFacts
{
    #[MongoDB\Id]
    private ?string $id = null;

    /**
     * Code-barres EAN-8 ou EAN-13. Indexé et unique pour une recherche rapide côté cache.
     */
    #[MongoDB\Field(type: 'string')]
    #[MongoDB\UniqueIndex]
    private string $codeBarres;

    #[MongoDB\Field(type: 'string')]
    private string $nom;

    #[MongoDB\Field(type: 'string', nullable: true)]
    private ?string $photoUrl = null;

    #[MongoDB\Field(type: 'string', nullable: true)]
    private ?string $categorie = null;

    /**
     * @var string[]
     */
    #[MongoDB\Field(type: 'collection')]
    private array $allergenes = [];

    #[MongoDB\Field(type: 'string', nullable: true)]
    private ?string $nutriscore = null;

    /**
     * JSON complet renvoyé par l'API OpenFoodFacts, conservé tel quel.
     */
    #[MongoDB\Field(type: 'hash')]
    private array $donneesBrutes = [];

    #[MongoDB\Field(type: 'date_immutable')]
    private \DateTimeImmutable $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getCodeBarres(): string
    {
        return $this->codeBarres;
    }

    public function setCodeBarres(string $codeBarres): static
    {
        $this->codeBarres = $codeBarres;
        return $this;
    }

    public function getNom(): string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;
        return $this;
    }

    public function getPhotoUrl(): ?string
    {
        return $this->photoUrl;
    }

    public function setPhotoUrl(?string $photoUrl): static
    {
        $this->photoUrl = $photoUrl;
        return $this;
    }

    public function getCategorie(): ?string
    {
        return $this->categorie;
    }

    public function setCategorie(?string $categorie): static
    {
        $this->categorie = $categorie;
        return $this;
    }

    /**
     * @return string[]
     */
    public function getAllergenes(): array
    {
        return $this->allergenes;
    }

    /**
     * @param string[] $allergenes
     */
    public function setAllergenes(array $allergenes): static
    {
        $this->allergenes = $allergenes;
        return $this;
    }

    public function getNutriscore(): ?string
    {
        return $this->nutriscore;
    }

    public function setNutriscore(?string $nutriscore): static
    {
        $this->nutriscore = $nutriscore;
        return $this;
    }

    public function getDonneesBrutes(): array
    {
        return $this->donneesBrutes;
    }

    public function setDonneesBrutes(array $donneesBrutes): static
    {
        $this->donneesBrutes = $donneesBrutes;
        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
