<?php

namespace App\Entity;

use App\Repository\PreferencesRegimeRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Table pivot preferences <-> regime_alimentaire (avec created_at).
 */
#[ORM\Entity(repositoryClass: PreferencesRegimeRepository::class)]
#[ORM\Table(name: 'preferences_regime')]
#[ORM\UniqueConstraint(name: 'uniq_preferences_regime', columns: ['preferences_id', 'regime_id'])]
#[ORM\HasLifecycleCallbacks]
class PreferencesRegime
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'preferencesRegimes', targetEntity: Preferences::class)]
    #[ORM\JoinColumn(name: 'preferences_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?Preferences $preferences = null;

    #[ORM\ManyToOne(targetEntity: RegimeAlimentaire::class)]
    #[ORM\JoinColumn(name: 'regime_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?RegimeAlimentaire $regime = null;

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

    public function getRegime(): ?RegimeAlimentaire
    {
        return $this->regime;
    }

    public function setRegime(?RegimeAlimentaire $regime): static
    {
        $this->regime = $regime;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }
}
