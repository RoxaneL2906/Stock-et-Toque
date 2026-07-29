import { useState, useEffect } from 'react';
import { Search, Plus } from 'lucide-react';
import HeaderAppli from '../../components/HeaderAppli/HeaderAppli';
import FooterNav from '../../components/FooterNav/FooterNav';
import CarteRecette from '../../components/CarteRecette/CarteRecette';
import { listerMesRecettes } from '../../services/recetteApi';
import './MesRecettesScreen.css';

const TABS = [
  { valeur: 'toutes', label: 'Toutes' },
  { valeur: 'publique', label: 'Publiques' },
  { valeur: 'privee', label: 'Privées' },
  { valeur: 'brouillons', label: 'Brouillons' },
];

function MesRecettesScreen({ onNaviguer, onNaviguerVersRecette }) {
  const [ongletActif, setOngletActif] = useState('toutes');
  const [recherche, setRecherche] = useState('');
  const [recettes, setRecettes] = useState([]);
  const [chargement, setChargement] = useState(true);
  const [erreur, setErreur] = useState('');

  useEffect(() => {
    setChargement(true);

    let visibilite = null;
    let brouillon = null;

    if (ongletActif === 'publique') {
      visibilite = 'publique';
      brouillon = false;
    } else if (ongletActif === 'privee') {
      visibilite = 'privee';
      brouillon = false;
    } else if (ongletActif === 'brouillons') {
      brouillon = true;
    }

    listerMesRecettes(visibilite, brouillon)
      .then((donnees) => {
        setRecettes(donnees);
        setChargement(false);
      })
      .catch((err) => {
        setErreur(err.message);
        setChargement(false);
      });
  }, [ongletActif]);

  const recettesFiltrees = recettes.filter((r) =>
    r.titre.toLowerCase().includes(recherche.toLowerCase())
  );

  if (erreur) {
    return <p style={{ color: '#EF4444' }}>{erreur}</p>;
  }

  return (
    <div className="ecran-complet">
      <HeaderAppli />
      <div className="ecran-contenu">

        <div className="mes-recettes-entete">
          <h2 className="mes-recettes-titre">Mes recettes</h2>
          <button className="mes-recettes-bouton-ajouter" onClick={() => onNaviguer('ajouterRecette')}>
            Ajouter <Plus size={16} color="#111827" />
          </button>
        </div>

        <div className="mes-recettes-tabs" style={{ flexShrink: 0 }}>
          {TABS.map((tab) => (
            <button
              key={tab.valeur}
              className={ongletActif === tab.valeur ? 'mes-recettes-tab-actif' : 'mes-recettes-tab'}
              onClick={() => setOngletActif(tab.valeur)}
            >
              {tab.label}
            </button>
          ))}
        </div>

        <div className="mes-recettes-barre-recherche">
          <Search size={18} color="#9CA3AF" />
          <input
            type="text"
            placeholder="Rechercher une recette...."
            value={recherche}
            onChange={(e) => setRecherche(e.target.value)}
          />
        </div>

        {chargement ? (
          <p style={{ color: '#fff' }}>Chargement...</p>
        ) : recettesFiltrees.length === 0 ? (
          <p style={{ color: 'var(--couleur-texte-clair)' }}>Aucune recette pour l'instant.</p>
        ) : (
          recettesFiltrees.map((recette) => (
            <CarteRecette
              key={recette.id}
              recette={recette}
              onClick={() => onNaviguerVersRecette(recette.id)}
            />
          ))
        )}

      </div>
      <FooterNav pageActive="recettes" onNaviguer={onNaviguer} />
    </div>
  );
}

export default MesRecettesScreen;