import { useState, useEffect } from 'react';
import { AlertTriangle } from 'lucide-react';
import HeaderAppli from '../../components/HeaderAppli/HeaderAppli';
import FooterNav from '../../components/FooterNav/FooterNav';
import { recupererProfil } from '../../services/profilApi';
import { recupererStock } from '../../services/stockApi';
import { rechercherRecettesPubliques } from '../../services/recetteApi';
import { consulterSemainePlanning } from '../../services/planningApi';
import { obtenirLundiDeSemaine, formaterDateApi, creneauEstPasse } from '../../utils/dateSemaine';
import photoDefaut from '../../assets/images/recetteDefaut.png';
import './AccueilConnecteScreen.css';

const JOURS_ENUM = ['lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi', 'dimanche'];
const MOMENTS = ['midi', 'soir'];

function AccueilConnecteScreen({ onNaviguer, onNaviguerVersRecettePublique }) {
  const [prenom, setPrenom] = useState('');
  const [produitsExpires, setProduitsExpires] = useState([]);
  const [produitsBientot, setProduitsBientot] = useState([]);
  const [prochainRepas, setProchainRepas] = useState(null);
  const [suggestions, setSuggestions] = useState([]);
  const [chargement, setChargement] = useState(true);

  useEffect(() => {
    const lundi = obtenirLundiDeSemaine();

    Promise.all([
      recupererProfil(),
      recupererStock(),
      rechercherRecettesPubliques({}),
      consulterSemainePlanning(formaterDateApi(lundi)),
    ])
      .then(([profil, stock, recettes, planning]) => {
        setPrenom(profil.prenom);
        setProduitsExpires(stock.filter((p) => p.alerte === 'rouge'));
        setProduitsBientot(stock.filter((p) => p.alerte === 'orange'));
        setSuggestions(recettes.slice(0, 2));
        setProchainRepas(trouverProchainRepas(lundi, planning.creneaux));
        setChargement(false);
      })
      .catch(() => setChargement(false));
  }, []);

  const trouverProchainRepas = (lundi, creneaux) => {
    for (let indexJour = 0; indexJour < 7; indexJour++) {
      for (const moment of MOMENTS) {
        if (creneauEstPasse(lundi, indexJour, moment)) continue;

        const creneau = creneaux.find(
          (c) => c.jour === JOURS_ENUM[indexJour] && c.moment === moment
        );

        if (creneau) {
          return { ...creneau, indexJour };
        }
      }
    }
    return null;
  };

  const libelleMoment = (repas) => {
    const aujourdhui = new Date();
    const indexAujourdhui = (aujourdhui.getDay() + 6) % 7;

    if (repas.indexJour === indexAujourdhui) {
      return repas.moment === 'midi' ? "Ce midi" : "Ce soir";
    }
    if (repas.indexJour === (indexAujourdhui + 1) % 7) {
      return repas.moment === 'midi' ? "Demain midi" : "Demain soir";
    }

    const nomJourCapitalise = JOURS_ENUM[repas.indexJour].charAt(0).toUpperCase() + JOURS_ENUM[repas.indexJour].slice(1);
    return `${nomJourCapitalise} ${repas.moment}`;
  };

  const clicProchainRepas = () => {
    if (prochainRepas.recette) {
      onNaviguerVersRecettePublique(prochainRepas.recette.id);
    } else {
      onNaviguer('planning');
    }
  };

  if (chargement) {
    return <p style={{ color: '#fff' }}>Chargement...</p>;
  }

  return (
    <div className="ecran-complet">
      <HeaderAppli />
      <div className="ecran-contenu">

        <h2 className="accueil-connecte-bonjour">Bonjour, {prenom} !</h2>

        {produitsExpires.length > 0 && (
          <div className="accueil-connecte-alerte accueil-connecte-alerte-urgent">
            <div className="accueil-connecte-alerte-texte">
              <p className="accueil-connecte-alerte-titre">Urgent</p>
              <p className="accueil-connecte-alerte-message">
                {produitsExpires.length > 1 ? 'Plusieurs produits sont périmés !' : 'Un produit est périmé !'}
              </p>
              <p className="accueil-connecte-alerte-liste">
                {produitsExpires.slice(0, 3).map((p) => p.nom).join(', ')}
              </p>
            </div>
            <AlertTriangle size={22} color="#EF4444" />
          </div>
        )}

        {produitsBientot.length > 0 && (
          <div className="accueil-connecte-alerte accueil-connecte-alerte-bientot">
            <div className="accueil-connecte-alerte-texte">
              <p className="accueil-connecte-alerte-titre-orange">Bientôt</p>
              <p className="accueil-connecte-alerte-message">Des produits seront bientôt périmés</p>
              <span className="accueil-connecte-lien-vert" onClick={() => onNaviguer('stock')}>
                Voir la liste
              </span>
            </div>
          </div>
        )}

        <div className="accueil-connecte-section-entete">
          <h3 className="accueil-connecte-section-titre">Prochain Repas</h3>
          <span className="accueil-connecte-lien-vert" onClick={() => onNaviguer('planning')}>
            Planning →
          </span>
        </div>

        {prochainRepas ? (
          <div className="accueil-connecte-repas-carte" onClick={clicProchainRepas}>
            <img
              src={(prochainRepas.recette && prochainRepas.recette.photo) || photoDefaut}
              alt=""
            />
            <div className="accueil-connecte-repas-overlay">
              <p className="accueil-connecte-repas-titre">
                {prochainRepas.recette ? prochainRepas.recette.titre : prochainRepas.platLibre}
              </p>
              <p className="accueil-connecte-repas-temps">🕐 {libelleMoment(prochainRepas)}</p>
            </div>
          </div>
        ) : (
          <p style={{ color: 'var(--couleur-texte-clair)', marginBottom: 16 }}>
            Aucun repas prévu pour l'instant.
          </p>
        )}

        {suggestions.length > 0 && (
          <>
            <div className="accueil-connecte-section-entete">
              <h3 className="accueil-connecte-section-titre">Suggestions</h3>
              <span className="accueil-connecte-lien-vert" onClick={() => onNaviguer('recettes')}>
                Voir tout →
              </span>
            </div>
            <div className="accueil-connecte-suggestions-grid">
              {suggestions.map((recette) => (
                <div
                  key={recette.id}
                  className="accueil-connecte-suggestion-carte"
                  onClick={() => onNaviguerVersRecettePublique(recette.id)}
                >
                  <img src={recette.photo || photoDefaut} alt={recette.titre} />
                  <p>{recette.titre}</p>
                </div>
              ))}
            </div>
          </>
        )}

      </div>
      <FooterNav pageActive="accueil" onNaviguer={onNaviguer} />
    </div>
  );
}

export default AccueilConnecteScreen;