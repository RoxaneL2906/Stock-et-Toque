import { useState, useEffect } from 'react';
import { AlertTriangle } from 'lucide-react';
import HeaderAppli from '../../components/HeaderAppli/HeaderAppli';
import FooterNav from '../../components/FooterNav/FooterNav';
import { recupererProfil } from '../../services/profilApi';
import { recupererStock } from '../../services/stockApi';
import { rechercherRecettesPubliques } from '../../services/recetteApi';
import photoDefaut from '../../assets/images/recetteDefaut.png';
import './AccueilConnecteScreen.css';

function AccueilConnecteScreen({ onNaviguer, onNaviguerVersRecettePublique }) {
  const [prenom, setPrenom] = useState('');
  const [produitsExpires, setProduitsExpires] = useState([]);
  const [produitsBientot, setProduitsBientot] = useState([]);
  const [prochainRepas, setProchainRepas] = useState(null);
  const [suggestions, setSuggestions] = useState([]);
  const [chargement, setChargement] = useState(true);

  useEffect(() => {
    Promise.all([
      recupererProfil(),
      recupererStock(),
      rechercherRecettesPubliques({}),
    ])
      .then(([profil, stock, recettes]) => {
        setPrenom(profil.prenom);
        setProduitsExpires(stock.filter((p) => p.alerte === 'rouge'));
        setProduitsBientot(stock.filter((p) => p.alerte === 'orange'));
        setProchainRepas(recettes.length > 0 ? recettes[0] : null);
        setSuggestions(recettes.slice(1, 3));
        setChargement(false);
      })
      .catch(() => setChargement(false));
  }, []);

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

        {prochainRepas && (
          <>
            <div className="accueil-connecte-section-entete">
              <h3 className="accueil-connecte-section-titre">Prochain Repas</h3>
              <span className="accueil-connecte-lien-vert" onClick={() => onNaviguer('planning')}>
                Planning →
              </span>
            </div>
            <div className="accueil-connecte-repas-carte" onClick={() => onNaviguerVersRecettePublique(prochainRepas.id)}>
              <img src={prochainRepas.photo || photoDefaut} alt={prochainRepas.titre} />
              <div className="accueil-connecte-repas-overlay">
                <p className="accueil-connecte-repas-titre">{prochainRepas.titre}</p>
                <p className="accueil-connecte-repas-temps">🕐 {prochainRepas.tempsPreparation} mn</p>
              </div>
            </div>
          </>
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