<?php

namespace App\Tests\Service;

use App\Enum\JourSemaineEnum;
use App\Enum\MomentEnum;
use App\Service\PlanningService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Teste la méthode creneauEstPasse du PlanningService,
 * qui détermine si un créneau (jour + moment d'une semaine) est déjà passé.
 * Règle : un créneau "midi" est passé à partir de 14h, un créneau "soir" à partir de 22h.
 */
class PlanningServiceTest extends KernelTestCase
{
    private PlanningService $planningService;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->planningService = static::getContainer()->get(PlanningService::class);
    }

    public function testCreneauDansLePasseEstDetecteCommePasse(): void
    {
        // Une semaine largement passée (janvier 2020) doit toujours être considérée comme passée
        $semaineDebut = new \DateTime('2020-01-06'); // un lundi
        $estPasse = $this->planningService->creneauEstPasse($semaineDebut, JourSemaineEnum::LUNDI, MomentEnum::MIDI);

        $this->assertTrue($estPasse, "Un créneau situé en janvier 2020 doit être considéré comme passé.");
    }

    public function testCreneauDansLeFuturNestPasDetecteCommePasse(): void
    {
        // Une semaine largement future (janvier 2030) ne doit jamais être considérée comme passée
        $semaineDebut = new \DateTime('2030-01-07'); // un lundi
        $estPasse = $this->planningService->creneauEstPasse($semaineDebut, JourSemaineEnum::LUNDI, MomentEnum::SOIR);

        $this->assertFalse($estPasse, "Un créneau situé en janvier 2030 ne doit pas être considéré comme passé.");
    }
}