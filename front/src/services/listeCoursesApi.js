import { appelApi } from './authApi';

export function recupererListeCourses() {
  return appelApi('/liste-courses', null, 'GET');
}

export function ajouterArticleListe(donnees) {
  return appelApi('/liste-courses', donnees);
}

export function basculerCocheArticle(id) {
  return appelApi(`/liste-courses/${id}/basculer`, {});
}

export function supprimerArticleListe(id) {
  return appelApi(`/liste-courses/${id}`, null, 'DELETE');
}

export function archiverArticlesCoches(ajouterAuStock) {
  return appelApi('/liste-courses/archiver', { ajouterAuStock });
}