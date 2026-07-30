import { useState, useEffect } from 'react';
import { ChevronLeft, ChevronRight, ChevronDown, ChevronUp, Plus, Pencil, Trash2 } from 'lucide-react';
import HeaderAppli from '../../components/HeaderAppli/HeaderAppli';
import FooterNav from '../../components/FooterNav/FooterNav';
import ModalSuppression from '../../components/ModalSuppression/ModalSuppression';
import { consulterSemainePlanning, definirCreneau, supprimerCreneau } from '../../services/planningApi';
import {
  obtenirLundiDeSemaine,
  formaterDateApi,
  obtenirJoursSemaine,
  cleJour,
  nomJour,
  formaterJourMois,
  numeroSemaineISO,
  decalerSemaine,
  estAujourdhui,
} from '../../utils/dateSemaine';
import photoDefaut from '../../assets/images/recetteDefaut.png';
import './PlanningScreen.css';

const MOMENTS = ['midi', 'soir'];

function PlanningScreen({ onNaviguer, onNaviguerVersChoixRecette, onNaviguerVersRecettePublique }) {
  const [lundiActuel, setLundiActuel] = useState(obtenirLundiDeSemaine());
  const [creneaux, setCreneaux] = useState([]);
  const [chargement, setChargement] = useState(true);
  const [erreur, setErreur] = useState('');
  const [joursOuverts, setJoursOuverts] = useState([]);

  const [modalChoixOuverte, setModalChoixOuverte] = useState(null);
  const [modePlatLibre, setModePlatLibre] = useState(false);
  const [platLibreNom, setPlatLibreNom] = useState('');
  const [platLibreUrl, setPlatLibreUrl] = useState('');

  const [creneauASupprimer, setCreneauASupprimer] = useState(null);

  const joursSemaine = obtenirJoursSemaine(lundiActuel);
  const estSemaineActuelle = formaterDateApi(lundiActuel) === formaterDateApi(obtenirLundiDeSemaine());

  const chargerPlanning = () => {
    setChargement(true);
    consulterSemainePlanning(formaterDateApi(lundiActuel))
      .then((donnees) => {
        setCreneaux(donnees.creneaux);
        setChargement(false);
      })
      .catch((err) => {
        setErreur(err.message);
        setChargement(false);
      });
  };

  useEffect(() => {
    chargerPlanning();

    if (estSemaineActuelle) {
      const indexAujourdhui = joursSemaine.findIndex((j) => estAujourdhui(j));
      setJoursOuverts(indexAujourdhui !== -1 ? [indexAujourdhui] : []);
    } else {
      setJoursOuverts([]);
    }
  }, [lundiActuel]);

  const changerSemaine = (delta) => {
    setLundiActuel(decalerSemaine(lundiActuel, delta));
  };

  const basculerJour = (indexJour) => {
    setJoursOuverts((prev) =>
      prev.includes(indexJour) ? prev.filter((j) => j !== indexJour) : [...prev, indexJour]
    );
  };

  const trouverCreneau = (indexJour, moment) => {
    const jourEnum = cleJour(indexJour);
    return creneaux.find((c) => c.jour === jourEnum && c.moment === moment) || null;
  };

  const ouvrirChoix = (indexJour, moment) => {
    setModalChoixOuverte({ jour: indexJour, moment });
    setModePlatLibre(false);
    setPlatLibreNom('');
    setPlatLibreUrl('');
  };

  const choisirRecherche = () => {
    onNaviguerVersChoixRecette({
      semaineDebut: formaterDateApi(lundiActuel),
      jour: cleJour(modalChoixOuverte.jour),
      moment: modalChoixOuverte.moment,
    });
  };

  const validerPlatLibre = async () => {
    if (platLibreNom.trim() === '') return;

    try {
      await definirCreneau({
        semaineDebut: formaterDateApi(lundiActuel),
        jour: cleJour(modalChoixOuverte.jour),
        moment: modalChoixOuverte.moment,
        platLibre: platLibreNom,
        urlSource: platLibreUrl || null,
      });
      setModalChoixOuverte(null);
      chargerPlanning();
    } catch (err) {
      setErreur(err.message);
    }
  };

  const confirmerSuppressionCreneau = async () => {
    await supprimerCreneau(creneauASupprimer);
    setCreneauASupprimer(null);
    chargerPlanning();
  };

  if (erreur) {
    return <p style={{ color: '#EF4444' }}>{erreur}</p>;
  }

  return (
    <div className="ecran-complet">
      <HeaderAppli />
      <div className="ecran-contenu">

        <div className="planning-entete-semaine">
          <div>
            <h2 className="planning-titre-semaine">Semaine {numeroSemaineISO(lundiActuel)}</h2>
            <p className="planning-plage-dates">
              {formaterJourMois(joursSemaine[0])} - {formaterJourMois(joursSemaine[6])}
            </p>
          </div>
          <div className="planning-navigation">
            <button onClick={() => changerSemaine(-1)} aria-label="Semaine précédente">
              <ChevronLeft size={18} color="#FFFFFF" />
            </button>
            <button onClick={() => changerSemaine(1)} aria-label="Semaine suivante">
              <ChevronRight size={18} color="#FFFFFF" />
            </button>
          </div>
        </div>

        {chargement ? (
          <p style={{ color: '#fff' }}>Chargement...</p>
        ) : (
          joursSemaine.map((jourDate, indexJour) => {
            const estOuvert = joursOuverts.includes(indexJour);

            return (
              <div key={indexJour} className={estOuvert ? 'planning-jour-carte planning-jour-carte-ouverte' : 'planning-jour-carte'}>
                <button
                  className="planning-jour-entete"
                  onClick={() => basculerJour(indexJour)}
                >
                  <span className="planning-jour-nom">
                    {nomJour(indexJour)} <span className="planning-jour-date">{formaterJourMois(jourDate)}</span>
                  </span>
                  {estOuvert ? (
                    <ChevronUp size={18} color="var(--couleur-vert)" />
                  ) : (
                    <ChevronDown size={18} color="var(--couleur-texte-clair)" />
                  )}
                </button>

                {estOuvert && (
                  <div className="planning-jour-creneaux">
                    {MOMENTS.map((moment) => {
                      const creneau = trouverCreneau(indexJour, moment);

                      return (
                        <div key={moment} className="planning-creneau">
                          <span className="planning-creneau-badge">{moment.toUpperCase()}</span>

                          {creneau ? (
                            <div className="planning-creneau-rempli">
                              <div
                                className="planning-creneau-contenu"
                                onClick={() => creneau.recette && onNaviguerVersRecettePublique(creneau.recette.id)}
                              >
                                <img
                                  src={(creneau.recette && creneau.recette.photo) || photoDefaut}
                                  alt=""
                                  className="planning-creneau-photo"
                                />
                                <span className="planning-creneau-nom-plat">
                                  {creneau.recette ? creneau.recette.titre : creneau.platLibre}
                                </span>
                              </div>
                              <div className="planning-creneau-actions">
                                <button onClick={() => ouvrirChoix(indexJour, moment)} aria-label="Modifier">
                                  <Pencil size={14} color="var(--couleur-orange)" />
                                </button>
                                <button onClick={() => setCreneauASupprimer(creneau.id)} aria-label="Supprimer">
                                  <Trash2 size={14} color="#EF4444" />
                                </button>
                              </div>
                            </div>
                          ) : (
                            <button
                              className="planning-creneau-vide"
                              onClick={() => ouvrirChoix(indexJour, moment)}
                            >
                              <Plus size={20} color="var(--couleur-vert)" />
                            </button>
                          )}
                        </div>
                      );
                    })}
                  </div>
                )}
              </div>
            );
          })
        )}

      </div>

      {modalChoixOuverte && (
        <div className="planning-modal-overlay">
          <div className="planning-modal-fond" onClick={() => setModalChoixOuverte(null)} />
          <div className="planning-modal-carte">
            {!modePlatLibre ? (
              <>
                <h3 className="planning-modal-titre">Ajouter un repas</h3>
                <button className="planning-modal-option" onClick={choisirRecherche}>
                  Chercher une recette
                </button>
                <button className="planning-modal-option" onClick={() => setModePlatLibre(true)}>
                  Saisir un plat libre
                </button>
                <button className="planning-modal-annuler" onClick={() => setModalChoixOuverte(null)}>
                  Annuler
                </button>
              </>
            ) : (
              <>
                <h3 className="planning-modal-titre">Plat libre</h3>
                <input
                  type="text"
                  placeholder="Nom du plat"
                  value={platLibreNom}
                  onChange={(e) => setPlatLibreNom(e.target.value)}
                  className="planning-modal-input"
                />
                <input
                  type="text"
                  placeholder="Lien (optionnel)"
                  value={platLibreUrl}
                  onChange={(e) => setPlatLibreUrl(e.target.value)}
                  className="planning-modal-input"
                />
                <button className="planning-modal-option planning-modal-valider" onClick={validerPlatLibre}>
                  Valider
                </button>
                <button className="planning-modal-annuler" onClick={() => setModalChoixOuverte(null)}>
                  Annuler
                </button>
              </>
            )}
          </div>
        </div>
      )}

      {creneauASupprimer && (
        <ModalSuppression
          titre="Supprimer ce repas ?"
          description="Ce créneau sera vidé du planning."
          onConfirmer={confirmerSuppressionCreneau}
          onFermer={() => setCreneauASupprimer(null)}
        />
      )}

      <FooterNav pageActive="planning" onNaviguer={onNaviguer} />
    </div>
  );
}

export default PlanningScreen;