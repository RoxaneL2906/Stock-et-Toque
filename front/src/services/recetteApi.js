import { appelApi } from './authApi';

export function creerRecette(donnees) {
  return appelApi('/recettes', donnees);
}

export function listerMesRecettes(visibilite = null, brouillon = null) {
  const params = new URLSearchParams();
  if (visibilite) params.append('visibilite', visibilite);
  if (brouillon !== null) params.append('brouillon', brouillon);
  const suffixe = params.toString() ? `?${params.toString()}` : '';
  return appelApi(`/recettes/mes-recettes${suffixe}`, null, 'GET');
}

export function consulterMaRecette(id) {
  return appelApi(`/recettes/${id}`, null, 'GET');
}

export function modifierRecette(id, donnees) {
  return appelApi(`/recettes/${id}`, donnees, 'PUT');
}

export function supprimerRecette(id) {
  return appelApi(`/recettes/${id}`, null, 'DELETE');
}

export async function uploaderPhotoRecette(id, fichier) {
  const formData = new FormData();
  formData.append('photo', fichier);

  const BASE_URL = import.meta.env.VITE_API_URL;
  const reponse = await fetch(`${BASE_URL}/recettes/${id}/photo`, {
    method: 'POST',
    credentials: 'include',
    body: formData,
  });

  const resultat = await reponse.json();
  if (!reponse.ok) {
    throw new Error(resultat.message || 'Une erreur est survenue.');
  }
  return resultat;
}

export function listerEquipements() {
  return appelApi('/equipements', null, 'GET');
}

