import { useState, useEffect } from 'react';
import { Search, Minus, Plus } from 'lucide-react';
import HeaderAppli from '../../components/HeaderAppli/HeaderAppli';
import FooterNav from '../../components/FooterNav/FooterNav';
import FormInput from '../../components/FormInput/FormInput';
import PrimaryButton from '../../components/PrimaryButton/PrimaryButton';
import BoutonRetour from '../../components/BoutonRetour/BoutonRetour';
import { rechercherProduitOpenFoodFacts, ajouterAuStock } from '../../services/stockApi';
import './AjouterProduitScreen.css';

const EMPLACEMENTS = [
  { valeur: 'frigo', label: 'Frigo' },
  { valeur: 'placard', label: 'Placard' },
  { valeur: 'autre', label: 'Autre' },
];

const UNITES = [
  { valeur: 'unite', label: 'Unité' },
  { valeur: 'kg', label: 'Kg' },
  { valeur: 'l', label: 'L' },
  { valeur: 'pack', label: 'Pack' },
];

function AjouterProduitScreen({ onNaviguer }) {
  const [recherche, setRecherche] = useState('');
  const [resultatsRecherche, setResultatsRecherche] = useState([]);
  const [rechercheEnCours, setRechercheEnCours] = useState(false);

  const [nom, setNom] = useState('');
  const [photo, setPhoto] = useState(null);
  const [codeBarres, setCodeBarres] = useState(null);
  const [categorie, setCategorie] = useState(null);
  const [emplacement, setEmplacement] = useState('frigo');
  const [quantite, setQuantite] = useState(1);
  const [unite, setUnite] = useState('unite');
  const [dlc, setDlc] = useState('');

  const [erreur, setErreur] = useState('');
  const [succes, setSucces] = useState('');
  const [chargement, setChargement] = useState(false);

  useEffect(() => {
    if (recherche.trim().length < 3) {
      setResultatsRecherche([]);
      return;
    }

    setRechercheEnCours(true);
    const delai = setTimeout(() => {
      rechercherProduitOpenFoodFacts(recherche)
        .then((donnees) => setResultatsRecherche(donnees))
        .catch(() => setResultatsRecherche([]))
        .finally(() => setRechercheEnCours(false));
    }, 500);

    return () => clearTimeout(delai);
  }, [recherche]);

  const selectionnerResultat = (produit) => {
    setNom(produit.nom);
    setPhoto(produit.photo);
    setCodeBarres(produit.codeBarres);
    setCategorie(produit.categorie);
    setRecherche('');
    setResultatsRecherche([]);
  };

  const gererSoumission = async (e) => {
    e.preventDefault();
    setErreur('');
    setSucces('');
    setChargement(true);

    try {
      await ajouterAuStock({
        nom,
        quantite,
        emplacement,
        unite,
        dlc: dlc || null,
        codeBarres,
        photo,
        categorie,
      });
      setSucces('Produit ajouté au stock avec succès.');
      setTimeout(() => onNaviguer('stock'), 1200);
    } catch (err) {
      setErreur(err.message);
    } finally {
      setChargement(false);
    }
  };

  return (
    <div className="ecran-complet">
      <HeaderAppli />
      <div className="ecran-contenu">
        <BoutonRetour onClick={() => onNaviguer('stock')} />

        <div className="ajout-barre-recherche">
          <Search size={18} color="#9CA3AF" />
          <input
            type="text"
            placeholder="Trouver un produit...."
            value={recherche}
            onChange={(e) => setRecherche(e.target.value)}
          />
        </div>

        {recherche.trim().length >= 3 && (
          <div className="ajout-resultats-recherche">
            {rechercheEnCours && <p className="ajout-recherche-info">Recherche...</p>}
            {!rechercheEnCours && resultatsRecherche.length === 0 && (
              <p className="ajout-recherche-info">Aucun résultat trouvé.</p>
            )}
            {resultatsRecherche.map((produit, index) => (
              <button
                key={index}
                className="ajout-resultat-item"
                onClick={() => selectionnerResultat(produit)}
              >
                {produit.photo ? (
                  <img src={produit.photo} alt={produit.nom} className="ajout-resultat-photo" />
                ) : (
                  <div className="ajout-resultat-photo-defaut" />
                )}
                <span>{produit.nom}</span>
              </button>
            ))}
          </div>
        )}

        <h2 className="ajout-titre">Ajouter manuellement</h2>

        <form onSubmit={gererSoumission} className="ajout-formulaire">
          <FormInput label="Nom" value={nom} onChange={setNom} />

          <div className="ajout-champ">
            <label className="form-input-label">Catégorie</label>
            <div className="ajout-boutons-choix">
              {EMPLACEMENTS.map((option) => (
                <button
                  key={option.valeur}
                  type="button"
                  className={emplacement === option.valeur ? 'ajout-choix-actif' : 'ajout-choix'}
                  onClick={() => setEmplacement(option.valeur)}
                >
                  {option.label}
                </button>
              ))}
            </div>
          </div>

          <div className="ajout-champ">
            <label className="form-input-label">Quantité</label>
            <div className="ajout-quantite-controle">
              <button type="button" onClick={() => setQuantite(Math.max(1, quantite - 1))} aria-label="Diminuer">
                <Minus size={16} color="#FFFFFF" />
              </button>
              <span>{quantite}</span>
              <button type="button" onClick={() => setQuantite(quantite + 1)} aria-label="Augmenter">
                <Plus size={16} color="#FFFFFF" />
              </button>
            </div>
          </div>

          <div className="ajout-champ">
            <label className="form-input-label">Unité</label>
            <div className="ajout-boutons-choix">
              {UNITES.map((option) => (
                <button
                  key={option.valeur}
                  type="button"
                  className={unite === option.valeur ? 'ajout-choix-actif' : 'ajout-choix'}
                  onClick={() => setUnite(option.valeur)}
                >
                  {option.label}
                </button>
              ))}
            </div>
          </div>

          <div className="ajout-champ">
            <label className="form-input-label">Date limite de consommation</label>
            <div className="ajout-date-conteneur">
              <input
                type="date"
                value={dlc}
                onChange={(e) => setDlc(e.target.value)}
                className="ajout-date-input"
              />
            </div>
          </div>

          {erreur && <p className="message-erreur">{erreur}</p>}
          {succes && <p className="message-succes">{succes}</p>}

          <PrimaryButton texte={chargement ? 'Ajout...' : 'Ajouter au stock'} type="submit" />
        </form>

      </div>
      <FooterNav pageActive="stock" onNaviguer={onNaviguer} />
    </div>
  );
}

export default AjouterProduitScreen;