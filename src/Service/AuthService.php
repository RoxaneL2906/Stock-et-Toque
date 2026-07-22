<?php

namespace App\Service;

use App\Entity\Utilisateur;
use App\Entity\RefreshToken;
use App\Repository\UtilisateurRepository;
use App\Repository\RefreshTokenRepository;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Mailer\MailerInterface;


class AuthService
{
    public function __construct(
        private readonly UtilisateurRepository $utilisateurRepository,
        private readonly RefreshTokenRepository $refreshTokenRepository,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly ValidatorInterface $validator,
        private readonly EntityManagerInterface $entityManager,
        private readonly JWTTokenManagerInterface $jwtTokenManager,
        private readonly MailerInterface $mailer,
    ) {}

    /**
     * Inscrit un nouvel utilisateur.
     *
     * @return array{utilisateur: Utilisateur, accessToken: string} Les infos nécessaires
     *         pour connecter automatiquement l'utilisateur juste après.
     *
     * @throws \InvalidArgumentException si l'email existe déjà ou si les données sont invalides
     *         (le Controller convertira ces exceptions en réponses HTTP 422)
     */
    public function inscrire(string $prenom, string $nom, string $email, string $motDePasse, string $confirmationMotDePasse): array
    {
        // Email unique
        if ($this->utilisateurRepository->findOneByEmail($email) !== null) {
            throw new \InvalidArgumentException('Un compte existe déjà avec cette adresse email.');
        }

        $utilisateur = new Utilisateur();
        $utilisateur->setPrenom($prenom);
        $utilisateur->setNom($nom);
        $utilisateur->setEmail($email);
        $utilisateur->setRole(['ROLE_USER']);
        $utilisateur->setPlainPassword($motDePasse);
        $utilisateur->setConfirmationMotDePasse($confirmationMotDePasse);

        // Règles de complexité + correspondance mots de passe
        $violations = $this->validator->validate($utilisateur, groups: ['inscription']);
        if (count($violations) > 0) {
            $messages = [];
            foreach ($violations as $violation) {
                $messages[] = $violation->getMessage();
            }
            throw new \InvalidArgumentException(implode(' ', $messages));
        }

        // Hachage du mot de passe avant stockage (jamais en clair en base)
        $utilisateur->setMotDePasse(
            $this->passwordHasher->hashPassword($utilisateur, $motDePasse)
        );
        $utilisateur->eraseCredentials();

        $this->entityManager->persist($utilisateur);
        $this->entityManager->flush();

        // Connexion automatique après inscription -> génération de l'access token
        $accessToken = $this->jwtTokenManager->create($utilisateur);

        return [
            'utilisateur' => $utilisateur,
            'accessToken' => $accessToken,
        ];
    }

    /**
     * Connecte un utilisateur existant.
     *
     * @return array{utilisateur: Utilisateur, accessToken: string, refreshToken: ?string}
     *
     * @throws \InvalidArgumentException si l'email ou le mot de passe est incorrect
     *         (le Controller convertira cette exception en réponse HTTP 401)
     */
    public function connecter(string $email, string $motDePasse, bool $seSouvenirDeMoi): array
    {
        $utilisateur = $this->utilisateurRepository->findOneByEmail($email);

        // Même message d'erreur qu'il s'agisse d'un email inconnu ou d'un mauvais mot de passe
        // (évite de révéler si un email est inscrit ou non)
        if ($utilisateur === null || !$this->passwordHasher->isPasswordValid($utilisateur, $motDePasse)) {
            throw new \InvalidArgumentException('Email ou mot de passe incorrect.');
        }

        $accessToken = $this->jwtTokenManager->create($utilisateur);

        $refreshTokenValue = null;

        if ($seSouvenirDeMoi) {
            $refreshTokenValue = bin2hex(random_bytes(64));

            $refreshToken = new RefreshToken();
            $refreshToken->setUtilisateur($utilisateur);
            $refreshToken->setToken($refreshTokenValue);
            $refreshToken->setExpiration(new \DateTimeImmutable('+30 days'));

            $this->entityManager->persist($refreshToken);
            $this->entityManager->flush();
        }

        return [
            'utilisateur' => $utilisateur,
            'accessToken' => $accessToken,
            'refreshToken' => $refreshTokenValue,
        ];
    }

    /**
     * Déconnecte un utilisateur en supprimant son refresh token côté serveur.
     * Si aucun refresh token n'est fourni (utilisateur non "souvenu"), il n'y a rien à faire côté serveur :
     * la déconnexion se limite à la suppression des tokens côté client.
     */
    public function deconnecter(?string $refreshTokenValue): void
    {
        if ($refreshTokenValue === null) {
            return;
        }

        $refreshToken = $this->refreshTokenRepository->findOneByToken($refreshTokenValue);

        if ($refreshToken !== null) {
            $this->entityManager->remove($refreshToken);
            $this->entityManager->flush();
        }
    }

    /**
     * Demande une réinitialisation de mot de passe : génère un token temporaire
     * et envoie un email contenant le lien de réinitialisation.
     *
     * Ne révèle jamais si l'email existe ou non en base (même comportement dans les deux cas),
     * pour éviter qu'un tiers puisse deviner quels emails sont inscrits.
     */
    public function demanderReinitialisation(string $email): void
    {
        $utilisateur = $this->utilisateurRepository->findOneByEmail($email);

        if ($utilisateur === null) {
            return;
        }

        $token = bin2hex(random_bytes(32));

        $utilisateur->setTokenReinitialisation($token);
        $utilisateur->setTokenReinitialisationExpiration(new \DateTimeImmutable('+1 hour'));

        $this->entityManager->flush();

        $message = (new \Symfony\Component\Mime\Email())
            ->from('no-reply@stocketoque.fr')
            ->to($utilisateur->getEmail())
            ->subject('Réinitialisation de votre mot de passe - Stock & Toque')
            ->text("Voici votre code de réinitialisation : {$token}\n\nCe code est valable 1 heure.");

        $this->mailer->send($message);
    }

    /**
     * Réinitialise le mot de passe à partir d'un token valide et non expiré.
     *
     * @throws \InvalidArgumentException si le token est invalide/expiré ou si les données sont invalides
     *         (le Controller convertira ces exceptions en réponses HTTP 422)
     */
    public function reinitialiserMotDePasse(string $token, string $nouveauMotDePasse, string $confirmationNouveauMotDePasse): void
    {
        $utilisateur = $this->utilisateurRepository->findOneByTokenReinitialisation($token);

        if (
            $utilisateur === null
            || $utilisateur->getTokenReinitialisationExpiration() === null
            || $utilisateur->getTokenReinitialisationExpiration() < new \DateTimeImmutable()
        ) {
            throw new \InvalidArgumentException('Ce lien de réinitialisation est invalide ou a expiré.');
        }

        $utilisateur->setPlainPassword($nouveauMotDePasse);
        $utilisateur->setConfirmationMotDePasse($confirmationNouveauMotDePasse);

        $violations = $this->validator->validate($utilisateur, groups: ['changement_mdp']);
        if (count($violations) > 0) {
            $messages = [];
            foreach ($violations as $violation) {
                $messages[] = $violation->getMessage();
            }
            throw new \InvalidArgumentException(implode(' ', $messages));
        }

        $utilisateur->setMotDePasse(
            $this->passwordHasher->hashPassword($utilisateur, $nouveauMotDePasse)
        );
        $utilisateur->eraseCredentials();

        // Token à usage unique : on l'invalide après utilisation
        $utilisateur->setTokenReinitialisation(null);
        $utilisateur->setTokenReinitialisationExpiration(null);

        $this->entityManager->flush();
    }
}
