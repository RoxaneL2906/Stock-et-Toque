<?php

namespace App\Repository;

use App\Entity\PreferencesAllergie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PreferencesAllergie>
 */
class PreferencesAllergieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PreferencesAllergie::class);
    }
}
