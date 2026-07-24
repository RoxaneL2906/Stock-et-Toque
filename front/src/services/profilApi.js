import { appelApi } from './authApi';

export function recupererProfil() {
  return appelApi('/profil', null, 'GET');
}