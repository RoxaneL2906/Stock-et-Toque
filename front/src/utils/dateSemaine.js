const JOURS_SEMAINE = ['lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi', 'dimanche'];
const NOMS_JOURS = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'];
const NOMS_MOIS = ['janv.', 'févr.', 'mars', 'avr.', 'mai', 'juin', 'juil.', 'août', 'sept.', 'oct.', 'nov.', 'déc.'];

/**
 * Retourne le lundi de la semaine contenant la date donnée (ou aujourd'hui si non précisé)
 */
export function obtenirLundiDeSemaine(date = new Date()) {
  const copie = new Date(date);
  const jourSemaine = copie.getDay(); // 0 = dimanche, 1 = lundi, ...
  const decalage = jourSemaine === 0 ? -6 : 1 - jourSemaine;
  copie.setDate(copie.getDate() + decalage);
  copie.setHours(0, 0, 0, 0);
  return copie;
}

/**
 * Formate une date en YYYY-MM-DD (format attendu par l'API)
 */
export function formaterDateApi(date) {
  const annee = date.getFullYear();
  const mois = String(date.getMonth() + 1).padStart(2, '0');
  const jour = String(date.getDate()).padStart(2, '0');
  return `${annee}-${mois}-${jour}`;
}

/**
 * Retourne les 7 objets Date de la semaine (lundi à dimanche) à partir du lundi de la semaine
 */
export function obtenirJoursSemaine(lundi) {
  return Array.from({ length: 7 }, (_, i) => {
    const jour = new Date(lundi);
    jour.setDate(jour.getDate() + i);
    return jour;
  });
}

/**
 * Nom de la clé enum backend pour un index de jour (0 = lundi, ..., 6 = dimanche)
 */
export function cleJour(index) {
  return JOURS_SEMAINE[index];
}

/**
 * Nom affiché pour un index de jour.
 */
export function nomJour(index) {
  return NOMS_JOURS[index];
}

/**
 * Formate une date en "23 oct."
 */
export function formaterJourMois(date) {
  return `${date.getDate()} ${NOMS_MOIS[date.getMonth()]}`;
}

/**
 * Calcule le numéro de semaine ISO 8601 d'une date.
 */
export function numeroSemaineISO(date) {
  const copie = new Date(Date.UTC(date.getFullYear(), date.getMonth(), date.getDate()));
  const jourSemaine = copie.getUTCDay() || 7;
  copie.setUTCDate(copie.getUTCDate() + 4 - jourSemaine);
  const debutAnnee = new Date(Date.UTC(copie.getUTCFullYear(), 0, 1));
  return Math.ceil((((copie - debutAnnee) / 86400000) + 1) / 7);
}

/**
 * Décale une date de lundi de semaine (+7 ou -7 jours).
 */
export function decalerSemaine(lundi, delta) {
  const copie = new Date(lundi);
  copie.setDate(copie.getDate() + delta * 7);
  return copie;
}

/**
 * Vérifie si une date correspond à aujourd'hui (jour/mois/année).
 */
export function estAujourdhui(date) {
  const aujourdhui = new Date();
  return (
    date.getDate() === aujourdhui.getDate() &&
    date.getMonth() === aujourdhui.getMonth() &&
    date.getFullYear() === aujourdhui.getFullYear()
  );
}

/**
 * Vérifie si un créneau (jour + moment) d'une semaine donnée (lundi) est déjà passé, côté client.
 * Mêmes règles que le backend : midi passé à 14h, soir passé à 22h.
 */
export function creneauEstPasse(lundi, indexJour, moment) {
  const jourDate = new Date(lundi);
  jourDate.setDate(jourDate.getDate() + indexJour);
  const heureLimite = moment === 'midi' ? 14 : 22;
  jourDate.setHours(heureLimite, 0, 0, 0);
  return jourDate < new Date();
}