import { useState, useEffect } from 'react';
import { Search } from 'lucide-react';
import HeaderAppli from '../../components/HeaderAppli/HeaderAppli';
import FooterNav from '../../components/FooterNav/FooterNav';
import CarteRecette from '../../components/CarteRecette/CarteRecette';
import BoutonRetour from '../../components/BoutonRetour/BoutonRetour';
import { listerMesFavoris } from '../../services/recetteApi';
import './MesFavorisScreen.css';

function MesFavorisScreen({ onNaviguer, onNaviguerVersRecettePublique }) {
  const [recherche, setRecherche] = useState('');
  const [favoris, setFavoris] = useState([]);
  const [chargement, setChargement] = useState(true);
  const [erreur, setErreur] = useState('');

  useEffect(() => {
    listerMesFavoris()
      .then((donnees) => {
        setFavoris(donnees);
        setChargement(false);
      })
      .catch((err) => {
        setErreur(err.message);
        setChargement(false);
      });
  }, []);

  const favorisFiltres = favoris.filter((r) =>
    r.titre.toLowerCase().includes(recherche.toLowerCase())
  );

  if (erreur) {
    return <p style={{ color: '#EF4444' }}>{erreur}</p>;
  }

  return (
    <div className="ecran-complet">
      <HeaderAppli />
      <div className="ecran-contenu">
        <BoutonRetour onClick={() => onNaviguer('profil')} />

        <h2 className="mes-favoris-titre">Mes favoris</h2>

        <div className="mes-favoris-barre-recherche">
          <Search size={18} color="#9CA3AF" />
          <input
            type="text"
            placeholder="Rechercher dans mes favoris...."
            value={recherche}
            onChange={(e) => setRecherche(e.target.value)}
          />
        </div>

        {chargement ? (
          <p style={{ color: '#fff' }}>Chargement...</p>
        ) : favorisFiltres.length === 0 ? (
          <p style={{ color: 'var(--couleur-texte-clair)' }}>Aucune recette en favori pour l'instant.</p>
        ) : (
          favorisFiltres.map((recette) => (
            <CarteRecette
              key={recette.id}
              recette={recette}
              onClick={() => onNaviguerVersRecettePublique(recette.id)}
            />
          ))
        )}

      </div>
      <FooterNav pageActive="recettes" onNaviguer={onNaviguer} />
    </div>
  );
}

export default MesFavorisScreen;