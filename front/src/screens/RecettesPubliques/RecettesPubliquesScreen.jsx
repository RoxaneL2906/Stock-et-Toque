import { useState, useEffect } from 'react';
import { Search, Plus, ChevronDown, ChevronUp } from 'lucide-react';
import HeaderAppli from '../../components/HeaderAppli/HeaderAppli';
import FooterNav from '../../components/FooterNav/FooterNav';
import CarteRecette from '../../components/CarteRecette/CarteRecette';
import { rechercherRecettesPubliques } from '../../services/recetteApi';
import './RecettesPubliquesScreen.css';

function RecettesPubliquesScreen({ onNaviguer, onNaviguerVersRecettePublique }) {
  const [recherche, setRecherche] = useState('');
  const [filtresOuverts, setFiltresOuverts] = useState(false);
  const [tempsMax, setTempsMax] = useState('');
  const [nbPersonnes, setNbPersonnes] = useState(null);
  const [difficulte, setDifficulte] = useState(null);
  const [budgetMax, setBudgetMax] = useState('');

  const [recettes, setRecettes] = useState([]);
  const [chargement, setChargement] = useState(true);
  const [erreur, setErreur] = useState('');

  const chargerRecettes = () => {
    setChargement(true);
    rechercherRecettesPubliques({
      q: recherche || undefined,
      tempsMax: tempsMax || undefined,
      nbPersonnes: nbPersonnes || undefined,
      difficulte: difficulte || undefined,
      budgetMax: budgetMax || undefined,
    })
      .then((donnees) => {
        setRecettes(donnees);
        setChargement(false);
      })
      .catch((err) => {
        setErreur(err.message);
        setChargement(false);
      });
  };

  useEffect(() => {
    chargerRecettes();
  }, []);

  const gererRecherche = (e) => {
    e.preventDefault();
    chargerRecettes();
  };

  // Visuel uniquement : "suggestion du jour" et "la communauté adore" dépendent des préférences/notation, mises en bonus. 
  // On pioche parmi les résultats existants pour "la vitrine visuelle".
  const suggestionDuJour = recettes.length > 0 ? recettes[0] : null;
  const communauteAdore = recettes.slice(1, 4);

  if (erreur) {
    return <p style={{ color: '#EF4444' }}>{erreur}</p>;
  }

  return (
    <div className="ecran-complet">
      <HeaderAppli />
      <div className="ecran-contenu">

        <form onSubmit={gererRecherche} className="recettes-pub-barre-recherche">
          <Search size={18} color="#9CA3AF" />
          <input
            type="text"
            placeholder="Rechercher une recette...."
            value={recherche}
            onChange={(e) => setRecherche(e.target.value)}
          />
        </form>

        <button
          type="button"
          className="recettes-pub-bouton-filtres"
          onClick={() => setFiltresOuverts(!filtresOuverts)}
        >
          Filtres
          {filtresOuverts ? <ChevronUp size={18} color="#FFFFFF" /> : <ChevronDown size={18} color="#FFFFFF" />}
        </button>

        {filtresOuverts && (
          <div className="recettes-pub-filtres-panneau">
            <label className="form-input-label">Temps max (mn)</label>
            <input
              type="text"
              inputMode="numeric"
              value={tempsMax}
              onChange={(e) => setTempsMax(e.target.value.replace(/\D/g, ''))}
              className="recettes-pub-filtre-input"
              placeholder="Ex : 30"
            />

            <label className="form-input-label">Difficulté</label>
            <div className="recettes-pub-filtre-choix-grid">
              {['facile', 'moyen', 'difficile'].map((niveau) => (
                <button
                  type="button"
                  key={niveau}
                  className={difficulte === niveau ? 'recettes-pub-choix-actif' : 'recettes-pub-choix'}
                  onClick={() => setDifficulte(difficulte === niveau ? null : niveau)}
                >
                  {niveau.charAt(0).toUpperCase() + niveau.slice(1)}
                </button>
              ))}
            </div>

            <label className="form-input-label">Budget max (€)</label>
            <input
              type="text"
              inputMode="decimal"
              value={budgetMax}
              onChange={(e) => setBudgetMax(e.target.value)}
              className="recettes-pub-filtre-input"
              placeholder="Ex : 15"
            />

            <label className="form-input-label">Nombre de personnes</label>
            <input
              type="text"
              inputMode="numeric"
              value={nbPersonnes || ''}
              onChange={(e) => setNbPersonnes(e.target.value.replace(/\D/g, ''))}
              className="recettes-pub-filtre-input"
              placeholder="Ex : 4"
            />

            <button type="button" className="recettes-pub-bouton-appliquer" onClick={chargerRecettes}>
              Appliquer les filtres
            </button>
          </div>
        )}

        <button className="recettes-pub-bouton-ajouter" onClick={() => onNaviguer('ajouterRecette')}>
          <Plus size={18} color="var(--couleur-vert)" /> Ajouter une recette
        </button>

        <div className="recettes-pub-raccourcis">
          <button className="recettes-pub-raccourci" onClick={() => onNaviguer('mesRecettes')}>
            Mes recettes
          </button>
          <button className="recettes-pub-raccourci" onClick={() => onNaviguer('mesFavoris')}>
            Mes favoris
          </button>
        </div>

        {chargement ? (
          <p style={{ color: '#fff' }}>Chargement...</p>
        ) : (
          <>
            {suggestionDuJour && (
              <>
                <h3 className="recettes-pub-section-titre">Suggestion du jour</h3>
                <CarteRecette
                  recette={suggestionDuJour}
                  onClick={() => onNaviguerVersRecettePublique(suggestionDuJour.id)}
                />
              </>
            )}

            {communauteAdore.length > 0 && (
              <>
                <h3 className="recettes-pub-section-titre">La communauté adore</h3>
                {communauteAdore.map((recette) => (
                  <CarteRecette
                    key={recette.id}
                    recette={recette}
                    onClick={() => onNaviguerVersRecettePublique(recette.id)}
                  />
                ))}
              </>
            )}

            {recettes.length === 0 && (
              <p style={{ color: 'var(--couleur-texte-clair)' }}>Aucune recette trouvée.</p>
            )}
          </>
        )}

      </div>
      <FooterNav pageActive="recettes" onNaviguer={onNaviguer} />
    </div>
  );
}

export default RecettesPubliquesScreen;