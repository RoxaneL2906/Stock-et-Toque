<?php

namespace App\Repository;

use App\Entity\PreferencesRegime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PreferencesRegime>
 */
class PreferencesRegimeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PreferencesRegime::class);
    }
}
