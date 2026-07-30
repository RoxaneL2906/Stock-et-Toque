<?php

namespace App\Service;

use App\Entity\Creneau;
use App\Entity\Planning;
use App\Entity\Utilisateur;
use App\Enum\JourSemaineEnum;
use App\Enum\MomentEnum;
use App\Repository\CreneauRepository;
use App\Repository\PlanningRepository;
use App\Repository\RecetteRepository;
use Doctrine\ORM\EntityManagerInterface;

class PlanningService
{
    public function __construct(
        private readonly PlanningRepository $planningRepository,
        private readonly CreneauRepository $creneauRepository,
        private readonly RecetteRepository $recetteRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {}

    /**
     * Consulte le planning d'une semaine (le crée s'il n'existe pas encore, vide).
     * $semaineDebut doit être un lundi (validé côté Controller).
     */
    public function consulterSemaine(Utilisateur $utilisateur, \DateTimeInterface $semaineDebut): array
    {
        $planning = $this->recupererOuCreerPlanning($utilisateur, $semaineDebut);

        $creneaux = [];
        foreach ($planning->getCreneaux() as $creneau) {
            $creneaux[] = $this->formaterCreneau($creneau);
        }

        return [
            'semaineDebut' => $planning->getSemaineDebut()->format('Y-m-d'),
            'creneaux' => $creneaux,
        ];
    }

    /**
     * Ajoute ou remplace un créneau (un seul plat par jour/moment).
     * Si $urlSource pointe vers une recette de l'app, elle est automatiquement associée
     * et le plat n'est pas traité comme un plat libre.
     *
     * @throws \InvalidArgumentException si ni platLibre ni urlSource valide n'est fourni,
     *         ou si le créneau est déjà passé
     */
    public function definirCreneau(
        Utilisateur $utilisateur,
        \DateTimeInterface $semaineDebut,
        string $jour,
        string $moment,
        ?int $recetteId,
        ?string $platLibre,
        ?string $urlSource,
    ): void {
        $jourEnum = JourSemaineEnum::from($jour);
        $momentEnum = MomentEnum::from($moment);

        $planning = $this->recupererOuCreerPlanning($utilisateur, $semaineDebut);

        if ($this->creneauEstPasse($semaineDebut, $jourEnum, $momentEnum)) {
            throw new \InvalidArgumentException('Impossible de modifier un créneau déjà passé.');
        }

        // Si une URL est fournie et pointe vers une recette de l'app, on l'associe automatiquement
        if ($recetteId === null && $urlSource !== null) {
            $idDetecte = $this->detecterRecetteDepuisUrl($urlSource);
            if ($idDetecte !== null) {
                $recetteId = $idDetecte;
            }
        }

        if ($recetteId === null && ($platLibre === null || trim($platLibre) === '')) {
            throw new \InvalidArgumentException('Il faut renseigner une recette ou un plat libre.');
        }

        $recette = null;
        if ($recetteId !== null) {
            $recette = $this->recetteRepository->findOnePublique($recetteId);
            if ($recette === null) {
                throw new \InvalidArgumentException('Cette recette est introuvable ou n\'est plus publique.');
            }
        }

        $creneauExistant = $this->creneauRepository->findOneByPlanningJourEtMoment($planning, $jourEnum, $momentEnum);

        $creneau = $creneauExistant ?? new Creneau();
        $creneau->setPlanning($planning);
        $creneau->setJour($jourEnum);
        $creneau->setMoment($momentEnum);
        $creneau->setRecette($recette);
        $creneau->setPlatLibre($recette !== null ? null : $platLibre);
        $creneau->setUrlSource($recette !== null ? null : $urlSource);

        if ($creneauExistant === null) {
            $this->entityManager->persist($creneau);
        }

        $this->entityManager->flush();
    }

    /**
     * Supprime un créneau.
     *
     * @throws \InvalidArgumentException si le créneau n'existe pas, n'appartient pas à l'utilisateur,
     *         ou est déjà passé
     */
    public function supprimerCreneau(Utilisateur $utilisateur, int $creneauId): void
    {
        $creneau = $this->creneauRepository->findOneByIdEtUtilisateur($creneauId, $utilisateur);

        if ($creneau === null) {
            throw new \InvalidArgumentException('Ce créneau ne fait pas partie de votre planning.');
        }

        if ($this->creneauEstPasse($creneau->getPlanning()->getSemaineDebut(), $creneau->getJour(), $creneau->getMoment())) {
            throw new \InvalidArgumentException('Impossible de supprimer un créneau déjà passé.');
        }

        $this->entityManager->remove($creneau);
        $this->entityManager->flush();
    }

    /**
     * Récupère le planning d'une semaine, ou le crée vide s'il n'existe pas.
     */
    private function recupererOuCreerPlanning(Utilisateur $utilisateur, \DateTimeInterface $semaineDebut): Planning
    {
        $planning = $this->planningRepository->findOneByUtilisateurEtSemaine($utilisateur, $semaineDebut);

        if ($planning === null) {
            $planning = new Planning();
            $planning->setUtilisateur($utilisateur);
            $planning->setSemaineDebut($semaineDebut);
            $this->entityManager->persist($planning);
            $this->entityManager->flush();
        }

        return $planning;
    }

    /**
     * Extrait un ID de recette depuis une URL interne du type ".../recettes/publiques/123".
     * Retourne null si l'URL ne correspond pas à ce format.
     */
    private function detecterRecetteDepuisUrl(string $url): ?int
    {
        if (preg_match('#/recettes/publiques/(\d+)#', $url, $matches)) {
            return (int) $matches[1];
        }

        return null;
    }

    /**
     * Vérifie si un créneau (jour + moment d'une semaine donnée) est déjà passé par rapport à maintenant.
     */
    private function creneauEstPasse(\DateTimeInterface $semaineDebut, JourSemaineEnum $jour, MomentEnum $moment): bool
    {
        $indexJour = array_search($jour, JourSemaineEnum::cases());
        $dateJour = \DateTime::createFromInterface($semaineDebut);
        $dateJour->modify("+{$indexJour} days");

        $heureLimite = $moment === MomentEnum::MIDI ? '14:00' : '22:00';
        $dateJour->modify($heureLimite);

        return $dateJour < new \DateTime();
    }

    private function formaterCreneau(Creneau $creneau): array
    {
        return [
            'id' => $creneau->getId(),
            'jour' => $creneau->getJour()->value,
            'moment' => $creneau->getMoment()->value,
            'recette' => $creneau->getRecette() !== null ? [
                'id' => $creneau->getRecette()->getId(),
                'titre' => $creneau->getRecette()->getTitre(),
                'photo' => $creneau->getRecette()->getPhoto(),
            ] : null,
            'platLibre' => $creneau->getPlatLibre(),
            'urlSource' => $creneau->getUrlSource(),
        ];
    }
}