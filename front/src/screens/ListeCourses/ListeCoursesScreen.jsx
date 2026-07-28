import { useState, useEffect } from 'react';
import { Plus, X } from 'lucide-react';
import HeaderAppli from '../../components/HeaderAppli/HeaderAppli';
import FooterNav from '../../components/FooterNav/FooterNav';
import PrimaryButton from '../../components/PrimaryButton/PrimaryButton';
import ModalSuppression from '../../components/ModalSuppression/ModalSuppression';
import {
  recupererListeCourses,
  basculerCocheArticle,
  archiverArticlesCoches,
  supprimerArticleListe,
} from '../../services/listeCoursesApi';
import './ListeCoursesScreen.css';

const LIBELLES_CATEGORIES = {
  fruits_legumes: 'Fruits & Légumes',
  produits_frais: 'Produits Frais',
  epicerie: 'Épicerie',
  autre: 'Autre',
};

const ORDRE_CATEGORIES = ['fruits_legumes', 'produits_frais', 'epicerie', 'autre'];

function ListeCoursesScreen({ onNaviguer }) {
  const [articles, setArticles] = useState([]);
  const [chargement, setChargement] = useState(true);
  const [erreur, setErreur] = useState('');
  const [archivageEnCours, setArchivageEnCours] = useState(false);
  const [confirmationOuverte, setConfirmationOuverte] = useState(false);
  const [articleASupprimer, setArticleASupprimer] = useState(null);

  const chargerListe = () => {
    recupererListeCourses()
      .then((donnees) => {
        setArticles(donnees);
        setChargement(false);
      })
      .catch((err) => {
        setErreur(err.message);
        setChargement(false);
      });
  };

  useEffect(() => {
    chargerListe();
  }, []);

  const gererBascule = async (id) => {
    try {
      await basculerCocheArticle(id);
      chargerListe();
    } catch (err) {
      setErreur(err.message);
    }
  };

  const gererArchivage = async (ajouterAuStock) => {
    setArchivageEnCours(true);
    setConfirmationOuverte(false);
    try {
      await archiverArticlesCoches(ajouterAuStock);
      chargerListe();
    } catch (err) {
      setErreur(err.message);
    } finally {
      setArchivageEnCours(false);
    }
  };

  const confirmerSuppression = async () => {
    await supprimerArticleListe(articleASupprimer.id);
    setArticleASupprimer(null);
    chargerListe();
  };

  const articlesParCategorie = ORDRE_CATEGORIES.reduce((acc, cle) => {
    acc[cle] = articles.filter((a) => (a.categorieAchat || 'autre') === cle);
    return acc;
  }, {});

  const auMoinsUnCoche = articles.some((a) => a.coche);

  if (erreur) {
    return <p style={{ color: '#EF4444' }}>{erreur}</p>;
  }

  return (
    <div className="ecran-complet">
      <HeaderAppli />
      <div className="ecran-contenu">

        <div className="courses-entete">
          <h2 className="courses-titre">Liste de courses</h2>
          <button className="courses-bouton-ajout" onClick={() => onNaviguer('ajouterArticleListe')} aria-label="Ajouter un article">
            <Plus size={20} color="#FFFFFF" />
          </button>
        </div>

        {chargement ? (
          <p style={{ color: '#fff' }}>Chargement...</p>
        ) : articles.length === 0 ? (
          <p style={{ color: 'var(--couleur-texte-clair)' }}>Votre liste de courses est vide.</p>
        ) : (
          <>
            {ORDRE_CATEGORIES.map((cle) => {
              const articlesCategorie = articlesParCategorie[cle];
              if (articlesCategorie.length === 0) return null;

              return (
                <div className="courses-categorie" key={cle}>
                  <h3 className="courses-categorie-titre">{LIBELLES_CATEGORIES[cle]}</h3>
                  {articlesCategorie.map((article) => (
                    <div
                      key={article.id}
                      className={article.coche ? 'courses-article courses-article-coche' : 'courses-article'}
                    >
                      <label className="courses-article-label">
                        <input
                          type="checkbox"
                          checked={article.coche}
                          onChange={() => gererBascule(article.id)}
                          className="courses-checkbox"
                        />
                        {article.photo ? (
                          <img src={article.photo} alt={article.nom} className="courses-article-photo" />
                        ) : (
                          <div className="courses-article-photo-defaut" />
                        )}
                        <span className="courses-article-nom">{article.nom}</span>
                        <span className="courses-article-quantite">x{article.quantite}</span>
                      </label>
                      <button
                        className="courses-article-supprimer"
                        onClick={() => setArticleASupprimer(article)}
                        aria-label="Supprimer cet article"
                      >
                        <X size={16} color="#EF4444" />
                      </button>
                    </div>
                  ))}
                </div>
              );
            })}

            {auMoinsUnCoche && !confirmationOuverte && (
              <div className="courses-bouton-archiver-zone">
                <PrimaryButton
                  texte="Tout archiver"
                  onClick={() => setConfirmationOuverte(true)}
                />
              </div>
            )}

            {confirmationOuverte && (
              <div className="courses-confirmation-archivage">
                <p className="courses-confirmation-texte">Ajouter ces articles à votre stock ?</p>
                <div className="courses-confirmation-boutons">
                  <button
                    className="courses-confirmation-non"
                    onClick={() => gererArchivage(false)}
                    disabled={archivageEnCours}
                  >
                    Non, juste archiver
                  </button>
                  <button
                    className="courses-confirmation-oui"
                    onClick={() => gererArchivage(true)}
                    disabled={archivageEnCours}
                  >
                    Oui, ajouter au stock
                  </button>
                </div>
              </div>
            )}
          </>
        )}

      </div>

      {articleASupprimer && (
        <ModalSuppression
          titre="Supprimer cet article ?"
          description={`Voulez-vous vraiment retirer "${articleASupprimer.nom}" de votre liste de courses ?`}
          onConfirmer={confirmerSuppression}
          onFermer={() => setArticleASupprimer(null)}
        />
      )}

      <FooterNav pageActive="courses" onNaviguer={onNaviguer} />
    </div>
  );
}

export default ListeCoursesScreen;