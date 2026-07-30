import { useState, useEffect } from 'react';
import { Clock, ChefHat, Euro, Heart, Trash2, Pencil, ChevronLeft, ChevronRight } from 'lucide-react';
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
  comparerAvecStock,
} from '../../services/recetteApi';
import { ajouterArticleListe } from '../../services/listeCoursesApi';
import { recupererProfil } from '../../services/profilApi';
import { definirCreneau, consulterSemainePlanning } from '../../services/planningApi';
import {
  obtenirLundiDeSemaine,
  formaterDateApi,
  nomJour,
  formaterJourMois,
  decalerSemaine,
  creneauEstPasse,
} from '../../utils/dateSemaine';
import photoDefaut from '../../assets/images/recetteDefaut.png';
import './RecettePubliqueDetailScreen.css';

const JOURS_ENUM = ['lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi', 'dimanche'];
const MOMENTS = ['midi', 'soir'];

function RecettePubliqueDetailScreen({ recetteId, onNaviguer, creneauCible, onCreneauAjoute }) {
  const [recette, setRecette] = useState(null);
  const [chargement, setChargement] = useState(true);
  const [erreur, setErreur] = useState('');
  const [favoriEnCours, setFavoriEnCours] = useState(false);
  const [ajoutPlanningEnCours, setAjoutPlanningEnCours] = useState(false);

  const [modalPlanningOuverte, setModalPlanningOuverte] = useState(false);
  const [lundiModal, setLundiModal] = useState(obtenirLundiDeSemaine());
  const [creneauxModal, setCreneauxModal] = useState([]);
  const [erreurModal, setErreurModal] = useState('');

  const [modalStockOuverte, setModalStockOuverte] = useState(false);
  const [ingredientsManquants, setIngredientsManquants] = useState([]);
  const [ingredientsCoches, setIngredientsCoches] = useState([]);
  const [chargementStock, setChargementStock] = useState(false);
  const [ajoutListeEnCours, setAjoutListeEnCours] = useState(false);
  const [succesListe, setSuccesListe] = useState('');

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

  useEffect(() => {
    if (!modalPlanningOuverte) return;

    consulterSemainePlanning(formaterDateApi(lundiModal))
      .then((donnees) => setCreneauxModal(donnees.creneaux))
      .catch(() => setCreneauxModal([]));
  }, [modalPlanningOuverte, lundiModal]);

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

  const ajouterAuCreneau = async (semaineDebut, jour, moment) => {
    setAjoutPlanningEnCours(true);
    try {
      await definirCreneau({ semaineDebut, jour, moment, recetteId });
    } catch (err) {
      setErreur(err.message);
    } finally {
      setAjoutPlanningEnCours(false);
    }
  };

  const gererAjoutAuPlanning = async () => {
    if (creneauCible) {
      await ajouterAuCreneau(creneauCible.semaineDebut, creneauCible.jour, creneauCible.moment);
      onCreneauAjoute();
    } else {
      setErreurModal('');
      setLundiModal(obtenirLundiDeSemaine());
      setModalPlanningOuverte(true);
    }
  };

  const trouverCreneauModal = (indexJour, moment) => {
    const jourEnum = JOURS_ENUM[indexJour];
    return creneauxModal.find((c) => c.jour === jourEnum && c.moment === moment) || null;
  };

  const choisirCreneauModal = async (indexJour, moment) => {
    if (creneauEstPasse(lundiModal, indexJour, moment)) return;

    setErreurModal('');
    try {
      await definirCreneau({
        semaineDebut: formaterDateApi(lundiModal),
        jour: JOURS_ENUM[indexJour],
        moment,
        recetteId,
      });
      setModalPlanningOuverte(false);
    } catch (err) {
      setErreurModal(err.message);
    }
  };

  const gererVerifierStock = async () => {
    setChargementStock(true);
    setSuccesListe('');
    setModalStockOuverte(true);
    try {
      const manquants = await comparerAvecStock(recetteId);
      setIngredientsManquants(manquants);
      setIngredientsCoches(manquants.map((_, index) => index));
    } catch (err) {
      setErreur(err.message);
    } finally {
      setChargementStock(false);
    }
  };

  const basculerIngredientCoche = (index) => {
    setIngredientsCoches((prev) =>
      prev.includes(index) ? prev.filter((i) => i !== index) : [...prev, index]
    );
  };

  const confirmerAjoutListeCourses = async () => {
    setAjoutListeEnCours(true);
    try {
      for (const index of ingredientsCoches) {
        const ingredient = ingredientsManquants[index];
        await ajouterArticleListe({
          nom: ingredient.nom,
          quantite: Math.ceil(ingredient.quantite),
          categorieAchat: 'autre',
        });
      }
      setSuccesListe('Ingrédients ajoutés à votre liste de courses.');
      setTimeout(() => setModalStockOuverte(false), 1200);
    } catch (err) {
      setErreur(err.message);
    } finally {
      setAjoutListeEnCours(false);
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
        <BoutonRetour onClick={() => onNaviguer(creneauCible ? 'planning' : 'recettes')} />

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

        <div className="recette-pub-boutons-action">
          <button className="recette-pub-bouton-planning" onClick={gererVerifierStock}>
            Vérifier mon stock
          </button>
          <PrimaryButton
            texte={ajoutPlanningEnCours ? 'Ajout...' : 'Ajouter au planning'}
            onClick={gererAjoutAuPlanning}
          />
        </div>

        <h3 className="recette-pub-section-titre">Commentaires ({commentaires.length})</h3>

        <form onSubmit={gererAjoutCommentaire} className="recette-pub-commentaire-form">
          <div className="recette-pub-commentaire-zone-texte">
            <textarea
              className="recette-pub-commentaire-textarea"
              value={nouveauCommentaire}
              onChange={(e) => setNouveauCommentaire(e.target.value.slice(0, 500))}
              onKeyDown={(e) => {
                if (e.key === 'Enter' && !e.shiftKey) {
                  e.preventDefault();
                  gererAjoutCommentaire(e);
                }
              }}
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
                      onKeyDown={(e) => {
                        if (e.key === 'Enter' && !e.shiftKey) {
                          e.preventDefault();
                          validerEdition(commentaire.id);
                        }
                      }}
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
                    <div className="recette-pub-commentaire-auteur-date">
                      <span className="recette-pub-commentaire-auteur">{commentaire.auteur}</span>
                      <span className="recette-pub-commentaire-date">{commentaire.createdAt}</span>
                    </div>
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

      {modalStockOuverte && (
        <div className="planning-modal-overlay">
          <div className="planning-modal-fond" onClick={() => setModalStockOuverte(false)} />
          <div className="planning-modal-carte">
            <h3 className="planning-modal-titre">Vérifier mon stock</h3>

            {chargementStock ? (
              <p style={{ color: '#fff', textAlign: 'center' }}>Vérification...</p>
            ) : ingredientsManquants.length === 0 ? (
              <p style={{ color: 'var(--couleur-vert)', textAlign: 'center' }}>
                Vous avez déjà tout en stock ! 
              </p>
            ) : (
              <>
                <p className="recette-pub-stock-info">Ingrédients manquants ou insuffisants :</p>
                <div className="recette-pub-stock-liste">
                  {ingredientsManquants.map((ingredient, index) => (
                    <label key={index} className="recette-pub-stock-ligne">
                      <input
                        type="checkbox"
                        checked={ingredientsCoches.includes(index)}
                        onChange={() => basculerIngredientCoche(index)}
                        className="recette-detail-checkbox"
                      />
                      <span>
                        {ingredient.nom} - {Math.ceil(ingredient.quantite)}{ingredient.unite ? ` ${ingredient.unite}` : ''}
                      </span>
                    </label>
                  ))}
                </div>

                {succesListe && <p className="message-succes">{succesListe}</p>}

                <button
                  className="planning-modal-option planning-modal-valider"
                  onClick={confirmerAjoutListeCourses}
                  disabled={ajoutListeEnCours || ingredientsCoches.length === 0}
                >
                  {ajoutListeEnCours ? 'Ajout...' : 'Ajouter à la liste de courses'}
                </button>
              </>
            )}

            <button className="planning-modal-annuler" onClick={() => setModalStockOuverte(false)}>
              Fermer
            </button>
          </div>
        </div>
      )}

      {modalPlanningOuverte && (
        <div className="planning-modal-overlay">
          <div className="planning-modal-fond" onClick={() => setModalPlanningOuverte(false)} />
          <div className="planning-modal-carte planning-modal-carte-large">
            <h3 className="planning-modal-titre">Choisir un créneau</h3>

            <div className="planning-modal-nav-semaine">
              <button onClick={() => setLundiModal(decalerSemaine(lundiModal, -1))} aria-label="Semaine précédente">
                <ChevronLeft size={16} color="#FFFFFF" />
              </button>
              <span>
                {formaterJourMois(lundiModal)} - {formaterJourMois(decalerSemaine(lundiModal, 1))}
              </span>
              <button onClick={() => setLundiModal(decalerSemaine(lundiModal, 1))} aria-label="Semaine suivante">
                <ChevronRight size={16} color="#FFFFFF" />
              </button>
            </div>

            <div className="planning-modal-grille">
              {JOURS_ENUM.map((_, indexJour) => (
                <div key={indexJour} className="planning-modal-jour-colonne">
                  <span className="planning-modal-jour-label">{nomJour(indexJour).slice(0, 3)}</span>
                  {MOMENTS.map((moment) => {
                    const passe = creneauEstPasse(lundiModal, indexJour, moment);
                    const creneau = trouverCreneauModal(indexJour, moment);

                    let classe = 'planning-modal-case-libre';
                    if (passe) classe = 'planning-modal-case-passe';
                    else if (creneau) classe = 'planning-modal-case-occupee';

                    return (
                      <button
                        key={moment}
                        className={classe}
                        disabled={passe}
                        onClick={() => choisirCreneauModal(indexJour, moment)}
                        title={creneau ? (creneau.recette ? creneau.recette.titre : creneau.platLibre) : ''}
                      >
                        {moment === 'midi' ? 'M' : 'S'}
                      </button>
                    );
                  })}
                </div>
              ))}
            </div>

            <div className="planning-modal-legende">
              <span><span className="planning-modal-pastille planning-modal-pastille-verte" /> Libre</span>
              <span><span className="planning-modal-pastille planning-modal-pastille-orange" /> Réservé</span>
              <span><span className="planning-modal-pastille planning-modal-pastille-grise" /> Indisponible</span>
            </div>

            {erreurModal && <p className="message-erreur">{erreurModal}</p>}

            <button className="planning-modal-annuler" onClick={() => setModalPlanningOuverte(false)}>
              Annuler
            </button>
          </div>
        </div>
      )}

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