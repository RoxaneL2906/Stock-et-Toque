<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class OpenFoodFactsService
{
    private const URL_RECHERCHE = 'https://search.openfoodfacts.org/search';

    public function __construct(
        private readonly HttpClientInterface $httpClient,
    ) {}

    /**
     * Recherche des produits par nom sur l'API OpenFoodFacts.
     * Retourne une liste simplifiée : nom, photo, code-barres, catégorie.
     * En cas d'échec de l'API externe (panne, timeout...), retourne un tableau vide
     * plutôt que de planter : l'ajout manuel reste toujours possible sans pré-remplissage.
     *
     * @return array<int, array{nom: string, photo: ?string, codeBarres: ?string, categorie: ?string}>
     */
    public function rechercherParNom(string $recherche): array
    {
        try {
            $reponse = $this->httpClient->request('GET', self::URL_RECHERCHE, [
                'query' => [
                    'q' => $recherche,
                    'page_size' => 10,
                ],
                'headers' => [
                    'User-Agent' => 'StockEtToque/1.0 (Projet étudiant DWWM - contact: no-reply@stocketoque.fr)',
                ],
            ]);

            $donnees = $reponse->toArray(false);
        } catch (\Throwable $e) {
            return [];
        }

        $produits = $donnees['hits'] ?? [];

        $resultat = [];
        foreach ($produits as $produit) {
            if (empty($produit['product_name'])) {
                continue;
            }

            $resultat[] = [
                'nom' => $produit['product_name'],
                'photo' => $produit['image_front_small_url'] ?? $produit['image_url'] ?? null,
                'codeBarres' => $produit['code'] ?? null,
                'categorie' => $produit['categories'] ?? null,
            ];
        }

        return $resultat;
    }
}