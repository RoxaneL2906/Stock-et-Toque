import { appelApi } from './authApi';

export function consulterSemainePlanning(semaineDebut) {
  return appelApi(`/planning?semaineDebut=${semaineDebut}`, null, 'GET');
}

export function definirCreneau(donnees) {
  return appelApi('/planning/creneau', donnees);
}

export function supprimerCreneau(id) {
  return appelApi(`/planning/creneau/${id}`, null, 'DELETE');
}