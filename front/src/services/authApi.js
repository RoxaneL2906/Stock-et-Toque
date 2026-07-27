const BASE_URL = import.meta.env.VITE_API_URL;

async function appelApi(endpoint, donnees = null, methode = 'POST') {
  const options = {
    method: methode,
    credentials: 'include',
  };

  if (donnees !== null) {
    options.headers = { 'Content-Type': 'application/json' };
    options.body = JSON.stringify(donnees);
  }

  const reponse = await fetch(`${BASE_URL}${endpoint}`, options);
  const resultat = await reponse.json();

  if (!reponse.ok) {
    throw new Error(resultat.message || 'Une erreur est survenue.');
  }

  return resultat;
}

export function inscrire(donnees) {
  return appelApi('/inscription', donnees);
}

export function connecter(donnees) {
  return appelApi('/connexion', donnees);
}

export function deconnecter() {
  return appelApi('/deconnexion', {});
}

export function demanderReinitialisation(email) {
  return appelApi('/mot-de-passe-oublie', { email });
}

export function reinitialiserMotDePasse(donnees) {
  return appelApi('/reinitialiser-mot-de-passe', donnees);
}

export function rafraichirToken() {
  return appelApi('/rafraichir-token', {});
}

export { appelApi };