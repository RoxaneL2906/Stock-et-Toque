import { appelApi } from './authApi';

export function recupererStock(emplacement = null) {
  const suffixe = emplacement ? `?emplacement=${emplacement}` : '';
  return appelApi(`/stock${suffixe}`, null, 'GET');
}

export function ajusterQuantiteStock(id, delta) {
  return appelApi(`/stock/${id}/ajuster`, { delta }, 'POST');
}

export function modifierStock(id, donnees) {
  return appelApi(`/stock/${id}`, donnees, 'PUT');
}

export function supprimerStock(id) {
  return appelApi(`/stock/${id}`, null, 'DELETE');
}

export function rechercherProduitOpenFoodFacts(recherche) {
  return appelApi(`/stock/recherche-produit?q=${encodeURIComponent(recherche)}`, null, 'GET');
}

export function ajouterAuStock(donnees) {
  return appelApi('/stock', donnees);
}