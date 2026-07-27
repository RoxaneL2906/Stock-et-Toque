<?php

namespace App\Service;

use App\Entity\Utilisateur;
use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ProfilService
{
    public function __construct(
        private readonly UtilisateurRepository $utilisateurRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly ValidatorInterface $validator,
        private readonly MailerInterface $mailer,
    ) {}

    /**
     * Retourne les informations du profil de l'utilisateur connecté.
     */
    public function consulterProfil(Utilisateur $utilisateur): array
    {
        return [
            'id' => $utilisateur->getId(),
            'prenom' => $utilisateur->getPrenom(),
            'nom' => $utilisateur->getNom(),
            'email' => $utilisateur->getEmail(),
            'photoProfil' => $utilisateur->getPhotoProfil(),
            'dateInscription' => $utilisateur->getDateInscription()?->format('Y-m-d'),
        ];
    }

    /**
     * Modifie le prénom et le nom. Aucune confirmation par mot de passe nécessaire.
     *
     * @throws \InvalidArgumentException si le prénom ou le nom est vide
     *         (le Controller convertira cette exception en réponse HTTP 422)
     */
    public function modifierInformations(Utilisateur $utilisateur, string $prenom, string $nom): void
    {
        $utilisateur->setPrenom($prenom);
        $utilisateur->setNom($nom);

        $violations = $this->validator->validate($utilisateur);
        if (count($violations) > 0) {
            $messages = [];
            foreach ($violations as $violation) {
                $messages[] = $violation->getMessage();
            }
            throw new \InvalidArgumentException(implode(' ', $messages));
        }

        $this->entityManager->flush();
    }

    /**
     * Modifie l'email. Nécessite le mot de passe actuel pour confirmation.
     * Invalide les refresh tokens existants : l'utilisateur devra se reconnecter.
     *
     * @throws \InvalidArgumentException si le mot de passe actuel est incorrect,
     *         si le nouvel email est invalide, ou déjà utilisé par un autre compte
     *         (le Controller convertira cette exception en réponse HTTP 422)
     */
    public function modifierEmail(Utilisateur $utilisateur, string $nouvelEmail, string $motDePasseActuel): void
    {
        if (!$this->passwordHasher->isPasswordValid($utilisateur, $motDePasseActuel)) {
            throw new \InvalidArgumentException('Mot de passe incorrect.');
        }

        $utilisateurExistant = $this->utilisateurRepository->findOneByEmail($nouvelEmail);
        if ($utilisateurExistant !== null && $utilisateurExistant->getId() !== $utilisateur->getId()) {
            throw new \InvalidArgumentException('Un compte existe déjà avec cette adresse email.');
        }

        $utilisateur->setEmail($nouvelEmail);

        $violations = $this->validator->validate($utilisateur);
        if (count($violations) > 0) {
            $messages = [];
            foreach ($violations as $violation) {
                $messages[] = $violation->getMessage();
            }
            throw new \InvalidArgumentException(implode(' ', $messages));
        }

        $this->invaliderRefreshTokens($utilisateur);

        $this->entityManager->flush();
    }

    /**
     * Modifie le mot de passe. Nécessite l'ancien mot de passe.
     * Invalide les refresh tokens existants : l'utilisateur devra se reconnecter.
     * Envoie un email de confirmation une fois le changement effectué.
     *
     * @throws \InvalidArgumentException si l'ancien mot de passe est incorrect,
     *         ou si le nouveau mot de passe/sa confirmation ne respecte pas les règles
     *         (le Controller convertira cette exception en réponse HTTP 422)
     */
    public function modifierMotDePasse(
        Utilisateur $utilisateur,
        string $ancienMotDePasse,
        string $nouveauMotDePasse,
        string $confirmationNouveauMotDePasse,
    ): void {
        if (!$this->passwordHasher->isPasswordValid($utilisateur, $ancienMotDePasse)) {
            throw new \InvalidArgumentException('Mot de passe actuel incorrect.');
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

        $this->invaliderRefreshTokens($utilisateur);

        $this->entityManager->flush();

        // Email de confirmation envoyé après coup, pour informer que le changement a bien été pris en compte
        $message = (new Email())
            ->from('no-reply@stocketoque.fr')
            ->to($utilisateur->getEmail())
            ->subject('Votre mot de passe a été modifié - Stock & Toque')
            ->text('Votre mot de passe vient d\'être modifié avec succès. Si vous n\'êtes pas à l\'origine de cette action, contactez-nous immédiatement.');

        $this->mailer->send($message);
    }

    /**
     * Invalide tous les refresh tokens de l'utilisateur (déconnexion de sécurité
     * après modification d'une donnée sensible : email ou mot de passe).
     */
    private function invaliderRefreshTokens(Utilisateur $utilisateur): void
    {
        foreach ($utilisateur->getRefreshTokens() as $refreshToken) {
            $this->entityManager->remove($refreshToken);
        }
    }

    
    /**
     * Supprime définitivement le compte de l'utilisateur.
     * Les recettes/commentaires publics sont automatiquement anonymisés (SET NULL en base),
     * les autres données personnelles (stocks, listes, favoris...) sont supprimées en cascade.
     * Un email de confirmation est envoyé avant la suppression effective.
     *
     * @throws \InvalidArgumentException si le mot de passe est incorrect
     *         (le Controller convertira cette exception en réponse HTTP 422)
     */
    public function supprimerCompte(Utilisateur $utilisateur, string $motDePasse): void
    {
        if (!$this->passwordHasher->isPasswordValid($utilisateur, $motDePasse)) {
            throw new \InvalidArgumentException('Mot de passe incorrect.');
        }

        $emailUtilisateur = $utilisateur->getEmail();

        $this->entityManager->remove($utilisateur);
        $this->entityManager->flush();

        $message = (new Email())
            ->from('no-reply@stocketoque.fr')
            ->to($emailUtilisateur)
            ->subject('Votre compte a été supprimé - Stock & Toque')
            ->text('Votre compte Stock & Toque et toutes vos données personnelles ont bien été supprimés. Si vous n\'êtes pas à l\'origine de cette action, contactez-nous immédiatement.');

        $this->mailer->send($message);
    }
}
