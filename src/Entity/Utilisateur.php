<?php

namespace App\Entity;

use App\Repository\UtilisateurRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ORM\Entity(repositoryClass: UtilisateurRepository::class)]
#[ORM\Table(name: 'utilisateur')]
#[ORM\HasLifecycleCallbacks]
#[Assert\Callback(callback: 'validateConfirmationMotDePasse', groups: ['inscription', 'changement_mdp'])]
class Utilisateur implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: 'Le prénom est obligatoire.')]
    #[Assert\Length(max: 100, maxMessage: 'Le prénom ne doit pas dépasser {{ limit }} caractères.')]
    private ?string $prenom = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: 'Le nom est obligatoire.')]
    #[Assert\Length(max: 100, maxMessage: 'Le nom ne doit pas dépasser {{ limit }} caractères.')]
    private ?string $nom = null;

    #[ORM\Column(length: 180, unique: true)]
    #[Assert\NotBlank(message: "L'email est obligatoire.")]
    #[Assert\Email(message: "L'adresse email n'est pas valide.")]
    private ?string $email = null;

    /**
     * Mot de passe hashé (jamais le mot de passe en clair).
     */
    #[ORM\Column(name: 'mot_de_passe', length: 255)]
    private ?string $motDePasse = null;

    /**
     * Champ non persisté, utilisé uniquement pour la validation du mot de passe en clair
     * lors de l'inscription / changement de mot de passe (rempli par le Service, jamais stocké).
     */
    #[Assert\NotBlank(message: 'Le mot de passe est obligatoire.', groups: ['inscription', 'changement_mdp'])]
    #[Assert\Regex(
        pattern: '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/',
        message: 'Le mot de passe doit contenir au moins 8 caractères, une majuscule, une minuscule, un chiffre et un caractère spécial.',
        groups: ['inscription', 'changement_mdp']
    )]
    private ?string $plainPassword = null;

    /**
     * Champ non persisté, utilisé uniquement pour vérifier que la confirmation
     * correspond au mot de passe saisi. Jamais stocké en base.
     */
    #[Assert\NotBlank(message: 'La confirmation du mot de passe est obligatoire.', groups: ['inscription', 'changement_mdp'])]
    private ?string $confirmationMotDePasse = null;

    #[ORM\Column(name: 'photo_profil', length: 255, nullable: true)]
    private ?string $photoProfil = null;

    #[ORM\Column(name: 'date_inscription', type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $dateInscription = null;

    #[ORM\Column(name: 'token_reinitialisation', length: 255, nullable: true)]
    private ?string $tokenReinitialisation = null;

    #[ORM\Column(name: 'token_reinitialisation_expiration', type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $tokenReinitialisationExpiration = null;

    /**
     * Rôles Symfony Security : ROLE_USER, ROLE_ADMIN, ROLE_SUPER_ADMIN.
     */
    #[ORM\Column(type: Types::JSON)]
    private array $role = [];

    #[ORM\Column(name: 'questionnaire_complete', type: Types::BOOLEAN, options: ['default' => false])]
    private bool $questionnaireComplete = false;

    #[ORM\OneToOne(mappedBy: 'utilisateur', targetEntity: Preferences::class, cascade: ['persist', 'remove'])]
    private ?Preferences $preferences = null;

    #[ORM\OneToMany(mappedBy: 'utilisateur', targetEntity: Stock::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $stocks;

    #[ORM\OneToMany(mappedBy: 'utilisateur', targetEntity: ListeCourses::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $listesCourses;

    #[ORM\OneToMany(mappedBy: 'auteur', targetEntity: Recette::class)]
    private Collection $recettes;

    #[ORM\OneToMany(mappedBy: 'utilisateur', targetEntity: Note::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $notes;

    #[ORM\OneToMany(mappedBy: 'utilisateur', targetEntity: Favori::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $favoris;

    #[ORM\OneToMany(mappedBy: 'utilisateur', targetEntity: Planning::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $plannings;

    #[ORM\OneToMany(mappedBy: 'utilisateur', targetEntity: Coupon::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $coupons;

    #[ORM\OneToMany(mappedBy: 'utilisateur', targetEntity: RefreshToken::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $refreshTokens;

    #[ORM\OneToMany(mappedBy: 'utilisateur', targetEntity: PreferenceGout::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $preferencesGout;

    public function __construct()
    {
        $this->stocks = new ArrayCollection();
        $this->listesCourses = new ArrayCollection();
        $this->recettes = new ArrayCollection();
        $this->notes = new ArrayCollection();
        $this->favoris = new ArrayCollection();
        $this->plannings = new ArrayCollection();
        $this->coupons = new ArrayCollection();
        $this->refreshTokens = new ArrayCollection();
        $this->preferencesGout = new ArrayCollection();
    }

    #[ORM\PrePersist]
    public function setDateInscriptionValue(): void
    {
        if ($this->dateInscription === null) {
            $this->dateInscription = new \DateTimeImmutable();
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): static
    {
        $this->prenom = $prenom;
        return $this;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;
        return $this;
    }

    /**
     * Identifiant unique utilisé par Symfony Security (remplace getUsername()).
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @return list<string>
     */
    public function getRoles(): array
    {
        $roles = $this->role;
        // Garantit que chaque utilisateur a au moins ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRole(array $role): static
    {
        $this->role = $role;
        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->motDePasse;
    }

    public function setMotDePasse(string $motDePasse): static
    {
        $this->motDePasse = $motDePasse;
        return $this;
    }

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(?string $plainPassword): static
    {
        $this->plainPassword = $plainPassword;
        return $this;
    }

    public function getConfirmationMotDePasse(): ?string
    {
        return $this->confirmationMotDePasse;
    }

    public function setConfirmationMotDePasse(?string $confirmationMotDePasse): static
    {
        $this->confirmationMotDePasse = $confirmationMotDePasse;
        return $this;
    }

    /**
     * Efface les données sensibles temporaires (ex : plainPassword) après authentification.
     */
    public function eraseCredentials(): void
    {
        $this->plainPassword = null;
    }

    /**
     * Vérifie que le mot de passe et sa confirmation correspondent.
     * Appelée automatiquement par le Validator grâce à #[Assert\Callback].
     */
    public function validateConfirmationMotDePasse(ExecutionContextInterface $context): void
    {
        if ($this->plainPassword !== null && $this->plainPassword !== $this->confirmationMotDePasse) {
            $context->buildViolation('Les mots de passe ne correspondent pas.')
                ->atPath('confirmationMotDePasse')
                ->addViolation();
        }
    }

    public function getPhotoProfil(): ?string
    {
        return $this->photoProfil;
    }

    public function setPhotoProfil(?string $photoProfil): static
    {
        $this->photoProfil = $photoProfil;
        return $this;
    }

    public function getDateInscription(): ?\DateTimeImmutable
    {
        return $this->dateInscription;
    }

    public function getTokenReinitialisation(): ?string
    {
        return $this->tokenReinitialisation;
    }

    public function setTokenReinitialisation(?string $tokenReinitialisation): static
    {
        $this->tokenReinitialisation = $tokenReinitialisation;
        return $this;
    }

    public function getTokenReinitialisationExpiration(): ?\DateTimeImmutable
    {
        return $this->tokenReinitialisationExpiration;
    }

    public function setTokenReinitialisationExpiration(?\DateTimeImmutable $tokenReinitialisationExpiration): static
    {
        $this->tokenReinitialisationExpiration = $tokenReinitialisationExpiration;
        return $this;
    }

    public function isQuestionnaireComplete(): bool
    {
        return $this->questionnaireComplete;
    }

    public function setQuestionnaireComplete(bool $questionnaireComplete): static
    {
        $this->questionnaireComplete = $questionnaireComplete;
        return $this;
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

    /**
     * @return Collection<int, Stock>
     */
    public function getStocks(): Collection
    {
        return $this->stocks;
    }

    /**
     * @return Collection<int, ListeCourses>
     */
    public function getListesCourses(): Collection
    {
        return $this->listesCourses;
    }

    /**
     * @return Collection<int, Recette>
     */
    public function getRecettes(): Collection
    {
        return $this->recettes;
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
     * @return Collection<int, Planning>
     */
    public function getPlannings(): Collection
    {
        return $this->plannings;
    }

    /**
     * @return Collection<int, Coupon>
     */
    public function getCoupons(): Collection
    {
        return $this->coupons;
    }

    /**
     * @return Collection<int, RefreshToken>
     */
    public function getRefreshTokens(): Collection
    {
        return $this->refreshTokens;
    }

    /**
     * @return Collection<int, PreferenceGout>
     */
    public function getPreferencesGout(): Collection
    {
        return $this->preferencesGout;
    }
}
