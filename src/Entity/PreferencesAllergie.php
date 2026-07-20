<?php

namespace App\Entity;

use App\Repository\PreferencesAllergieRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Table pivot preferences <-> allergie (avec created_at).
 */
#[ORM\Entity(repositoryClass: PreferencesAllergieRepository::class)]
#[ORM\Table(name: 'preferences_allergie')]
#[ORM\UniqueConstraint(name: 'uniq_preferences_allergie', columns: ['preferences_id', 'allergie_id'])]
#[ORM\HasLifecycleCallbacks]
class PreferencesAllergie
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'preferencesAllergies', targetEntity: Preferences::class)]
    #[ORM\JoinColumn(name: 'preferences_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?Preferences $preferences = null;

    #[ORM\ManyToOne(targetEntity: Allergie::class)]
    #[ORM\JoinColumn(name: 'allergie_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?Allergie $allergie = null;

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

    public function getAllergie(): ?Allergie
    {
        return $this->allergie;
    }

    public function setAllergie(?Allergie $allergie): static
    {
        $this->allergie = $allergie;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }
}
