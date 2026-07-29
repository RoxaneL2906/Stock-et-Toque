import { useState, useEffect } from 'react';
import { Clock, ChefHat, Euro, Pencil, Trash2 } from 'lucide-react';
import HeaderAppli from '../../components/HeaderAppli/HeaderAppli';
import FooterNav from '../../components/FooterNav/FooterNav';
import ModalSuppression from '../../components/ModalSuppression/ModalSuppression';
import { consulterMaRecette, supprimerRecette } from '../../services/recetteApi';
import photoDefaut from '../../assets/images/recetteDefaut.png';
import './RecetteDetailScreen.css';

function RecetteDetailScreen({ recetteId, onNaviguer, onNaviguerVersModification }) {
  const [recette, setRecette] = useState(null);
  const [chargement, setChargement] = useState(true);
  const [erreur, setErreur] = useState('');
  const [modalSuppressionOuverte, setModalSuppressionOuverte] = useState(false);

  useEffect(() => {
    consulterMaRecette(recetteId)
      .then((donnees) => {
        setRecette(donnees);
        setChargement(false);
      })
      .catch((err) => {
        setErreur(err.message);
        setChargement(false);
      });
  }, [recetteId]);

  const confirmerSuppression = async () => {
    await supprimerRecette(recetteId);
    onNaviguer('mesRecettes');
  };

  if (chargement) {
    return <p style={{ color: '#fff' }}>Chargement...</p>;
  }

  if (erreur) {
    return <p style={{ color: '#EF4444' }}>{erreur}</p>;
  }

  const estRecettePublique = recette.visibilite === 'publique';

  return (
    <div className="ecran-complet">
      <HeaderAppli />
      <div className="ecran-contenu">

        <div className="recette-detail-entete">
          <h2 className="recette-detail-titre">{recette.titre}</h2>
          <div className="recette-detail-actions">
            <button onClick={() => onNaviguerVersModification()} aria-label="Modifier">
              <Pencil size={18} color="var(--couleur-orange)" />
            </button>
            <button onClick={() => setModalSuppressionOuverte(true)} aria-label="Supprimer">
              <Trash2 size={18} color="#EF4444" />
            </button>
          </div>
        </div>

        <div className="recette-detail-image">
          <img src={recette.photo || photoDefaut} alt={recette.titre} />
          <p className="recette-detail-image-titre">{recette.titre}</p>
        </div>

        <div className="recette-detail-infos-grid">
          <div className="recette-detail-info-case">
            <Clock size={16} color="var(--couleur-texte-clair)" />
            <span>{(recette.tempsPreparation || 0) + (recette.tempsCuisson || 0)} mn</span>
          </div>
          <div className="recette-detail-info-case">
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
          <div className="recette-detail-info-case">
            <Euro size={16} color="var(--couleur-texte-clair)" />
            <span>{recette.budgetEstime ? `${recette.budgetEstime} €` : '-'}</span>
          </div>
        </div>

        {recette.equipements.length > 0 && (
          <>
            <h3 className="recette-detail-section-titre">Équipements nécessaires</h3>
            <div className="recette-detail-equipements">
              {recette.equipements.map((equipement) => (
                <span key={equipement.id} className="recette-detail-equipement-pilule">
                  {equipement.nom}
                </span>
              ))}
            </div>
          </>
        )}

        <h3 className="recette-detail-section-titre">Ingrédients (pour {recette.nbPersonnes} pers.)</h3>
        <div className="recette-detail-ingredients">
          {recette.ingredients.map((ingredient) => (
            <label key={ingredient.id} className="recette-detail-ingredient-ligne">
              <input type="checkbox" className="recette-detail-checkbox" />
              <span>
                {ingredient.nom}
                {ingredient.quantite && ` - ${parseFloat(ingredient.quantite)}${ingredient.unite ? ' ' + ingredient.unite : ''}`}
              </span>
            </label>
          ))}
        </div>

        <h3 className="recette-detail-section-titre">Étapes</h3>
        <div className="recette-detail-etapes">
          {recette.etapes.map((etape) => (
            <div key={etape.ordre} className="recette-detail-etape-ligne">
              <span className="recette-detail-etape-numero">{etape.ordre + 1}</span>
              <p>{etape.description}</p>
            </div>
          ))}
        </div>

      </div>

      {modalSuppressionOuverte && (
        <ModalSuppression
          titre="Supprimer cette recette ?"
          description={
            estRecettePublique
              ? `"${recette.titre}" est publique. Une version anonymisée restera visible à la communauté, et vous récupérerez automatiquement une copie privée dans "Mes recettes".`
              : `Voulez-vous vraiment supprimer "${recette.titre}" ? Cette action est irréversible.`
          }
          onConfirmer={confirmerSuppression}
          onFermer={() => setModalSuppressionOuverte(false)}
        />
      )}

      <FooterNav pageActive="recettes" onNaviguer={onNaviguer} />
    </div>
  );
}

export default RecetteDetailScreen;