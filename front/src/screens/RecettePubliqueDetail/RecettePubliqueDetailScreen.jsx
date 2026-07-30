import { useState, useEffect } from 'react';
import { Clock, ChefHat, Euro, Heart, Trash2, Pencil } from 'lucide-react';
import HeaderAppli from '../../components/HeaderAppli/HeaderAppli';
import FooterNav from '../../components/FooterNav/FooterNav';
import BoutonRetour from '../../components/BoutonRetour/BoutonRetour';
import PrimaryButton from '../../components/PrimaryButton/PrimaryButton';
import ModalSuppression from '../../components/ModalSuppression/ModalSuppression';
import {
  consulterRecettePublique,
  basculerFavori,
  listerCommentaires,
  ajouterCommentaire,
  modifierCommentaire,
  supprimerCommentaire,
} from '../../services/recetteApi';
import { recupererProfil } from '../../services/profilApi';
import photoDefaut from '../../assets/images/recetteDefaut.png';
import './RecettePubliqueDetailScreen.css';

function RecettePubliqueDetailScreen({ recetteId, onNaviguer }) {
  const [recette, setRecette] = useState(null);
  const [chargement, setChargement] = useState(true);
  const [erreur, setErreur] = useState('');
  const [favoriEnCours, setFavoriEnCours] = useState(false);

  const [monId, setMonId] = useState(null);
  const [commentaires, setCommentaires] = useState([]);
  const [nouveauCommentaire, setNouveauCommentaire] = useState('');
  const [commentaireEnEdition, setCommentaireEnEdition] = useState(null);
  const [texteEdition, setTexteEdition] = useState('');
  const [commentaireASupprimer, setCommentaireASupprimer] = useState(null);
  const [envoiEnCours, setEnvoiEnCours] = useState(false);

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

    recupererProfil().then((profil) => setMonId(profil.id));

    chargerCommentaires();
  }, [recetteId]);

  const chargerCommentaires = () => {
    listerCommentaires(recetteId)
      .then(setCommentaires)
      .catch(() => setCommentaires([]));
  };

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

  const gererAjoutCommentaire = async (e) => {
    e.preventDefault();
    if (nouveauCommentaire.trim() === '') return;

    setEnvoiEnCours(true);
    try {
      await ajouterCommentaire(recetteId, nouveauCommentaire);
      setNouveauCommentaire('');
      chargerCommentaires();
    } catch (err) {
      setErreur(err.message);
    } finally {
      setEnvoiEnCours(false);
    }
  };

  const commencerEdition = (commentaire) => {
    setCommentaireEnEdition(commentaire.id);
    setTexteEdition(commentaire.contenu);
  };

  const validerEdition = async (id) => {
    try {
      await modifierCommentaire(id, texteEdition);
      setCommentaireEnEdition(null);
      chargerCommentaires();
    } catch (err) {
      setErreur(err.message);
    }
  };

  const confirmerSuppressionCommentaire = async () => {
    await supprimerCommentaire(commentaireASupprimer);
    setCommentaireASupprimer(null);
    chargerCommentaires();
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

        <h3 className="recette-pub-section-titre">Commentaires ({commentaires.length})</h3>

        <form onSubmit={gererAjoutCommentaire} className="recette-pub-commentaire-form">
          <div className="recette-pub-commentaire-zone-texte">
            <textarea
              className="recette-pub-commentaire-textarea"
              value={nouveauCommentaire}
              onChange={(e) => setNouveauCommentaire(e.target.value.slice(0, 500))}
              placeholder="Laissez un commentaire..."
            />
            <span className="recette-pub-commentaire-compteur-overlay">{nouveauCommentaire.length}/500</span>
          </div>
          <div className="recette-pub-commentaire-form-bas">
            <button type="submit" className="recette-pub-commentaire-envoyer" disabled={envoiEnCours}>
              Envoyer
            </button>
          </div>
        </form>

        <div className="recette-pub-commentaires-liste">
          {commentaires.map((commentaire) => (
            <div key={commentaire.id} className="recette-pub-commentaire-carte">
              {commentaireEnEdition === commentaire.id ? (
                <>
                  <div className="recette-pub-commentaire-zone-texte">
                    <textarea
                      className="recette-pub-commentaire-textarea"
                      value={texteEdition}
                      onChange={(e) => setTexteEdition(e.target.value.slice(0, 500))}
                    />
                    <span className="recette-pub-commentaire-compteur-overlay">{texteEdition.length}/500</span>
                  </div>
                  <div className="recette-pub-commentaire-form-bas">
                    <button
                      type="button"
                      className="recette-pub-commentaire-annuler"
                      onClick={() => setCommentaireEnEdition(null)}
                    >
                      Annuler
                    </button>
                    <button
                      type="button"
                      className="recette-pub-commentaire-envoyer"
                      onClick={() => validerEdition(commentaire.id)}
                    >
                      Valider
                    </button>
                  </div>
                </>
              ) : (
                <>
                  <div className="recette-pub-commentaire-entete">
                    <span className="recette-pub-commentaire-auteur">{commentaire.auteur}</span>
                    {commentaire.auteurId === monId && (
                      <div className="recette-pub-commentaire-actions">
                        <button onClick={() => commencerEdition(commentaire)} aria-label="Modifier">
                          <Pencil size={14} color="var(--couleur-orange)" />
                        </button>
                        <button onClick={() => setCommentaireASupprimer(commentaire.id)} aria-label="Supprimer">
                          <Trash2 size={14} color="#EF4444" />
                        </button>
                      </div>
                    )}
                  </div>
                  <p className="recette-pub-commentaire-contenu">{commentaire.contenu}</p>
                </>
              )}
            </div>
          ))}
        </div>

      </div>

      {commentaireASupprimer && (
        <ModalSuppression
          titre="Supprimer ce commentaire ?"
          description="Cette action est irréversible."
          onConfirmer={confirmerSuppressionCommentaire}
          onFermer={() => setCommentaireASupprimer(null)}
        />
      )}

      <FooterNav pageActive="recettes" onNaviguer={onNaviguer} />
    </div>
  );
}

export default RecettePubliqueDetailScreen;