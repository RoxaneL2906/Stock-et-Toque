import { appelApi } from './authApi';

export function recupererProfil() {
  return appelApi('/profil', null, 'GET');
}

export function modifierInformations(donnees) {
  return appelApi('/profil/informations', donnees);
}

export function modifierEmail(donnees) {
  return appelApi('/profil/email', donnees);
}

export function modifierMotDePasse(donnees) {
  return appelApi('/profil/mot-de-passe', donnees);
}