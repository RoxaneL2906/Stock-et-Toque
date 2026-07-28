const BASE_URL = import.meta.env.VITE_API_URL;

let rafraichissementEnCours = null;

async function appelApi(endpoint, donnees = null, methode = 'POST', viaRefresh = false) {
  const options = {
    method: methode,
    credentials: 'include',
  };

  if (donnees !== null) {
    options.headers = { 'Content-Type': 'application/json' };
    options.body = JSON.stringify(donnees);
  }

  const reponse = await fetch(`${BASE_URL}${endpoint}`, options);

  // Intercepteur : si le token a expiré (401) et qu'on n'a pas déjà essayé de le rafraîchir,
  // on tente un rafraîchissement automatique puis on rejoue la requête une seule fois.
  if (reponse.status === 401 && !viaRefresh && endpoint !== '/rafraichir-token') {
    try {
      await lancerRafraichissement();
      return appelApi(endpoint, donnees, methode, true);
    } catch {
      // Le rafraîchissement a échoué (refresh token expiré/absent) : on laisse l'erreur 401 remonter normalement
    }
  }

  const resultat = await reponse.json();

  if (!reponse.ok) {
    throw new Error(resultat.message || 'Une erreur est survenue.');
  }

  return resultat;
}

/**
 * Lance un rafraîchissement du token. Si plusieurs requêtes échouent en même temps (401 simultanés),
 * une seule vraie requête de rafraîchissement est envoyée : les autres attendent son résultat.
 */
function lancerRafraichissement() {
  if (rafraichissementEnCours === null) {
    rafraichissementEnCours = rafraichirToken().finally(() => {
      rafraichissementEnCours = null;
    });
  }
  return rafraichissementEnCours;
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