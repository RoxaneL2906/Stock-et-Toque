<?php

namespace App\Service;

use App\Entity\Utilisateur;
use App\Entity\RefreshToken;
use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;


class AuthService
{
    public function __construct(
        private readonly UtilisateurRepository $utilisateurRepository,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly ValidatorInterface $validator,
        private readonly EntityManagerInterface $entityManager,
        private readonly JWTTokenManagerInterface $jwtTokenManager,
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
}
