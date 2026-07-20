<?php

namespace App\Entity;

use App\Repository\PreferencesEquipementRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Table pivot preferences <-> equipement (avec created_at).
 */
#[ORM\Entity(repositoryClass: PreferencesEquipementRepository::class)]
#[ORM\Table(name: 'preferences_equipement')]
#[ORM\UniqueConstraint(name: 'uniq_preferences_equipement', columns: ['preferences_id', 'equipement_id'])]
#[ORM\HasLifecycleCallbacks]
class PreferencesEquipement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'preferencesEquipements', targetEntity: Preferences::class)]
    #[ORM\JoinColumn(name: 'preferences_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?Preferences $preferences = null;

    #[ORM\ManyToOne(targetEntity: Equipement::class)]
    #[ORM\JoinColumn(name: 'equipement_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?Equipement $equipement = null;

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

    public function getPreferences(): ?Preferences
    {
        return $this->preferences;
    }

    public function setPreferences(?Preferences $preferences): static
    {
        $this->preferences = $preferences;
        return $this;
    }

    public function getEquipement(): ?Equipement
    {
        return $this->equipement;
    }

    public function setEquipement(?Equipement $equipement): static
    {
        $this->equipement = $equipement;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }
}
