import { useState, useEffect } from 'react';
import { Clock, ChefHat, Euro, Heart } from 'lucide-react';
import HeaderAppli from '../../components/HeaderAppli/HeaderAppli';
import FooterNav from '../../components/FooterNav/FooterNav';
import BoutonRetour from '../../components/BoutonRetour/BoutonRetour';
import PrimaryButton from '../../components/PrimaryButton/PrimaryButton';
import { consulterRecettePublique, basculerFavori } from '../../services/recetteApi';
import photoDefaut from '../../assets/images/recetteDefaut.png';
import './RecettePubliqueDetailScreen.css';

function RecettePubliqueDetailScreen({ recetteId, onNaviguer }) {
  const [recette, setRecette] = useState(null);
  const [chargement, setChargement] = useState(true);
  const [erreur, setErreur] = useState('');
  const [favoriEnCours, setFavoriEnCours] = useState(false);

  useEffect(() => {
    consulterRecettePublique(recetteId)
      .then((donnees) => {
        setRecette(donnees);
        setChargement(false);
      })
      .catch((err) => {
        setErreur(err.message);
        setChargement(false);
      });
  }, [recetteId]);

  const gererFavori = async () => {
    setFavoriEnCours(true);
    try {
      const { estFavorite } = await basculerFavori(recetteId);
      setRecette({ ...recette, estFavorite });
    } catch (err) {
      setErreur(err.message);
    } finally {
      setFavoriEnCours(false);
    }
  };

  if (chargement) {
    return <p style={{ color: '#fff' }}>Chargement...</p>;
  }

  if (erreur) {
    return <p style={{ color: '#EF4444' }}>{erreur}</p>;
  }

  return (
    <div className="ecran-complet">
      <HeaderAppli />
      <div className="ecran-contenu">
        <BoutonRetour onClick={() => onNaviguer('recettes')} />

        <div className="recette-pub-entete">
          <h2 className="recette-pub-titre">{recette.titre}</h2>
          <button
            className="recette-pub-favori"
            onClick={gererFavori}
            disabled={favoriEnCours}
            aria-label="Ajouter aux favoris"
          >
            <Heart
              size={22}
              color="#EF4444"
              fill={recette.estFavorite ? '#EF4444' : 'none'}
            />
          </button>
        </div>

        <p className="recette-pub-auteur">Par {recette.auteur}</p>

        <div className="recette-pub-image">
          <img src={recette.photo || photoDefaut} alt={recette.titre} />
          <p className="recette-pub-image-titre">{recette.titre}</p>
        </div>

        <div className="recette-pub-infos-grid">
          <div className="recette-pub-info-case">
            <Clock size={16} color="var(--couleur-texte-clair)" />
            <span>{(recette.tempsPreparation || 0) + (recette.tempsCuisson || 0)} mn</span>
          </div>
          <div className="recette-pub-info-case">
            {[0, 1, 2].map((i) => {
              const nbToquesColorees =
                recette.difficulte === 'facile' ? 1 :
                recette.difficulte === 'moyen' ? 2 :
                recette.difficulte === 'difficile' ? 3 : 0;
              return (
                <ChefHat
                  key={i}
                  size={16}
                  color={i < nbToquesColorees ? 'var(--couleur-orange)' : '#4B5563'}
                />
              );
            })}
          </div>
          <div className="recette-pub-info-case">
            <Euro size={16} color="var(--couleur-texte-clair)" />
            <span>{recette.budgetEstime ? `${recette.budgetEstime} €` : '-'}</span>
          </div>
        </div>

        {recette.noteMoyenne !== null && (
          <p className="recette-pub-note">⭐ {recette.noteMoyenne.toFixed(1)} / 5</p>
        )}

        {recette.equipements.length > 0 && (
          <>
            <h3 className="recette-pub-section-titre">Équipements nécessaires</h3>
            <div className="recette-pub-equipements">
              {recette.equipements.map((equipement) => (
                <span key={equipement.id} className="recette-pub-equipement-pilule">
                  {equipement.nom}
                </span>
              ))}
            </div>
          </>
        )}

        <h3 className="recette-pub-section-titre">Ingrédients (pour {recette.nbPersonnes} pers.)</h3>
        <div className="recette-pub-ingredients">
          {recette.ingredients.map((ingredient) => (
            <div key={ingredient.id} className="recette-pub-ingredient-ligne">
              <span>
                {ingredient.nom}
                {ingredient.quantite && ` - ${parseFloat(ingredient.quantite)}${ingredient.unite ? ' ' + ingredient.unite : ''}`}
              </span>
            </div>
          ))}
        </div>

        <h3 className="recette-pub-section-titre">Étapes</h3>
        <div className="recette-pub-etapes">
          {recette.etapes.map((etape) => (
            <div key={etape.ordre} className="recette-pub-etape-ligne">
              <span className="recette-pub-etape-numero">{etape.ordre + 1}</span>
              <p>{etape.description}</p>
            </div>
          ))}
        </div>

        <PrimaryButton texte="Vérifier mon stock" onClick={() => {}} />

      </div>
      <FooterNav pageActive="recettes" onNaviguer={onNaviguer} />
    </div>
  );
}

export default RecettePubliqueDetailScreen;