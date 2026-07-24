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
     * Modifie le prénom et le nom (US 2.2 - CA1). Aucune confirmation par mot de passe nécessaire.
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
     * Modifie l'email (US 2.2 - CA2/CA3). Nécessite le mot de passe actuel pour confirmation.
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

        $this->entityManager->flush();
    }

    /**
     * Modifie le mot de passe (US 2.2 - CA4). Nécessite l'ancien mot de passe.
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

        $this->entityManager->flush();

        // Email de confirmation envoyé après coup, pour informer que le changement a bien été pris en compte
        $message = (new Email())
            ->from('no-reply@stocketoque.fr')
            ->to($utilisateur->getEmail())
            ->subject('Votre mot de passe a été modifié - Stock & Toque')
            ->text('Votre mot de passe vient d\'être modifié avec succès. Si vous n\'êtes pas à l\'origine de cette action, contactez-nous immédiatement.');

        $this->mailer->send($message);
    }
}