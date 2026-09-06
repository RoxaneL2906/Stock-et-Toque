<?php

namespace App\Entity;

use App\Enum\JourSemaineEnum;
use App\Repository\PreferencesRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PreferencesRepository::class)]
#[ORM\Table(name: 'preferences')]
#[ORM\HasLifecycleCallbacks]
class Preferences
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'preferences', targetEntity: Utilisateur::class)]
    #[ORM\JoinColumn(name: 'utilisateur_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?Utilisateur $utilisateur = null;

    #[ORM\Column(name: 'nb_personnes')]
    #[Assert\Positive(message: 'Le nombre de personnes doit être positif.')]
    private ?int $nbPersonnes = null;

    #[ORM\Column(name: 'budget_mensuel')]
    #[Assert\PositiveOrZero(message: 'Le budget mensuel doit être positif ou nul.')]
    private ?int $budgetMensuel = null;

    #[ORM\Column(name: 'jour_courses', length: 20, nullable: true, enumType: JourSemaineEnum::class)]
    private ?JourSemaineEnum $jourCourses = null;

    #[ORM\Column(name: 'created_at', type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $createdAt = null;

    /**
     * @var Collection<int, PreferencesRegime>
     */
    #[ORM\OneToMany(mappedBy: 'preferences', targetEntity: PreferencesRegime::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $preferencesRegimes;

    /**
     * @var Collection<int, PreferencesAllergie>
     */
    #[ORM\OneToMany(mappedBy: 'preferences', targetEntity: PreferencesAllergie::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $preferencesAllergies;

    /**
     * @var Collection<int, PreferencesEquipement>
     */
    #[ORM\OneToMany(mappedBy: 'preferences', targetEntity: PreferencesEquipement::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $preferencesEquipements;

    public function __construct()
    {
        $this->preferencesRegimes = new ArrayCollection();
        $this->preferencesAllergies = new ArrayCollection();
        $this->preferencesEquipements = new ArrayCollection();
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

    public function getUtilisateur(): ?Utilisateur
    {
        return $this->utilisateur;
    }

    public function setUtilisateur(?Utilisateur $utilisateur): static
    {
        $this->utilisateur = $utilisateur;
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

    public function getBudgetMensuel(): ?int
    {
        return $this->budgetMensuel;
    }

    public function setBudgetMensuel(int $budgetMensuel): static
    {
        $this->budgetMensuel = $budgetMensuel;
        return $this;
    }

    public function getJourCourses(): ?JourSemaineEnum
    {
        return $this->jourCourses;
    }

    public function setJourCourses(?JourSemaineEnum $jourCourses): static
    {
        $this->jourCourses = $jourCourses;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * @return Collection<int, PreferencesRegime>
     */
    public function getPreferencesRegimes(): Collection
    {
        return $this->preferencesRegimes;
    }

    /**
     * @return Collection<int, PreferencesAllergie>
     */
    public function getPreferencesAllergies(): Collection
    {
        return $this->preferencesAllergies;
    }

    /**
     * @return Collection<int, PreferencesEquipement>
     */
    public function getPreferencesEquipements(): Collection
    {
        return $this->preferencesEquipements;
    }
}
