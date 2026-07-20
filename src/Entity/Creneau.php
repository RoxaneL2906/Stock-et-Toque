<?php

namespace App\Entity;

use App\Enum\JourSemaineEnum;
use App\Enum\MomentEnum;
use App\Repository\CreneauRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Créneau de planning. Si url_source pointe vers une recette de l'app,
 * le Service de planning associe automatiquement recette_id (pas un plat libre).
 */
#[ORM\Entity(repositoryClass: CreneauRepository::class)]
#[ORM\Table(name: 'creneau')]
#[ORM\HasLifecycleCallbacks]
class Creneau
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'creneaux', targetEntity: Planning::class)]
    #[ORM\JoinColumn(name: 'planning_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?Planning $planning = null;

    #[ORM\ManyToOne(inversedBy: 'creneaux', targetEntity: Recette::class)]
    #[ORM\JoinColumn(name: 'recette_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?Recette $recette = null;

    #[ORM\Column(length: 20, enumType: JourSemaineEnum::class)]
    #[Assert\NotNull(message: 'Le jour est obligatoire.')]
    private ?JourSemaineEnum $jour = null;

    #[ORM\Column(length: 10, enumType: MomentEnum::class)]
    #[Assert\NotNull(message: 'Le moment (midi/soir) est obligatoire.')]
    private ?MomentEnum $moment = null;

    #[ORM\Column(name: 'plat_libre', length: 255, nullable: true)]
    private ?string $platLibre = null;

    #[ORM\Column(name: 'url_source', length: 500, nullable: true)]
    #[Assert\Url(message: "L'URL fournie n'est pas valide.")]
    private ?string $urlSource = null;

    #[ORM\Column(name: 'notification_envoyee', type: Types::BOOLEAN, options: ['default' => false])]
    private bool $notificationEnvoyee = false;

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

    public function getPlanning(): ?Planning
    {
        return $this->planning;
    }

    public function setPlanning(?Planning $planning): static
    {
        $this->planning = $planning;
        return $this;
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

    public function getJour(): ?JourSemaineEnum
    {
        return $this->jour;
    }

    public function setJour(JourSemaineEnum $jour): static
    {
        $this->jour = $jour;
        return $this;
    }

    public function getMoment(): ?MomentEnum
    {
        return $this->moment;
    }

    public function setMoment(MomentEnum $moment): static
    {
        $this->moment = $moment;
        return $this;
    }

    public function getPlatLibre(): ?string
    {
        return $this->platLibre;
    }

    public function setPlatLibre(?string $platLibre): static
    {
        $this->platLibre = $platLibre;
        return $this;
    }

    public function getUrlSource(): ?string
    {
        return $this->urlSource;
    }

    public function setUrlSource(?string $urlSource): static
    {
        $this->urlSource = $urlSource;
        return $this;
    }

    public function isNotificationEnvoyee(): bool
    {
        return $this->notificationEnvoyee;
    }

    public function setNotificationEnvoyee(bool $notificationEnvoyee): static
    {
        $this->notificationEnvoyee = $notificationEnvoyee;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }
}
