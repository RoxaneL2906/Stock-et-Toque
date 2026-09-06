<?php

namespace App\DocumentRepository;

use App\Document\ProduitOpenFoodFacts;
use Doctrine\ODM\MongoDB\Repository\DocumentRepository;

class ProduitOpenFoodFactsRepository extends DocumentRepository
{
    public function findOneByCodeBarres(string $codeBarres): ?ProduitOpenFoodFacts
    {
        return $this->findOneBy(['codeBarres' => $codeBarres]);
    }
}

