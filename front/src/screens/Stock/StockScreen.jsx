import { useState, useEffect, useMemo } from 'react';
import { Search, Plus, ArrowUpDown } from 'lucide-react';
import HeaderAppli from '../../components/HeaderAppli/HeaderAppli';
import FooterNav from '../../components/FooterNav/FooterNav';
import CarteProduitStock from '../../components/CarteProduitStock/CarteProduitStock';
import ModalSuppression from '../../components/ModalSuppression/ModalSuppression';
import { recupererStock, ajusterQuantiteStock, supprimerStock } from '../../services/stockApi';
import './StockScreen.css';

function StockScreen({ onNaviguer }) {
  const [produits, setProduits] = useState([]);
  const [chargement, setChargement] = useState(true);
  const [erreur, setErreur] = useState('');
  const [ongletActif, setOngletActif] = useState('tout');
  const [recherche, setRecherche] = useState('');
  const [produitASupprimer, setProduitASupprimer] = useState(null);

  const chargerStock = () => {
    const emplacement = ongletActif === 'tout' ? null : ongletActif;
    recupererStock(emplacement)
      .then((donnees) => {
        setProduits(donnees);
        setChargement(false);
      })
      .catch((err) => {
        setErreur(err.message);
        setChargement(false);
      });
  };

  useEffect(() => {
    setChargement(true);
    chargerStock();
  }, [ongletActif]);

  const gererAjustement = async (id, delta) => {
    try {
      const nouvelleQuantite = await ajusterQuantiteStock(id, delta);
      if (nouvelleQuantite.quantite === 0) {
        const produit = produits.find((p) => p.id === id);
        setProduitASupprimer(produit);
      }
      chargerStock();
    } catch (err) {
      setErreur(err.message);
    }
  };

  const confirmerSuppression = async () => {
    await supprimerStock(produitASupprimer.id);
    setProduitASupprimer(null);
    chargerStock();
  };

  const produitsFiltres = useMemo(() => {
    let resultat = produits;

    if (recherche.trim() !== '') {
      resultat = resultat.filter((p) =>
        p.nom.toLowerCase().includes(recherche.toLowerCase())
      );
    }

    return [...resultat].sort((a, b) => {
      const dateA = a.dlc || a.ddm || '9999-99-99';
      const dateB = b.dlc || b.ddm || '9999-99-99';
      return dateA.localeCompare(dateB);
    });
  }, [produits, recherche]);

  const produitsBientot = produitsFiltres.filter((p) => p.alerte !== 'aucune');
  const tousLesProduits = produitsFiltres;

  const titreSection = ongletActif === 'tout'
    ? 'Tout mon stock'
    : `Inventaire ${ongletActif.charAt(0).toUpperCase()}${ongletActif.slice(1)}`;

  if (erreur) {
    return <p style={{ color: '#EF4444' }}>{erreur}</p>;
  }

  return (
    <div className="ecran-complet">
      <HeaderAppli />
      <div className="ecran-contenu">

        <div className="stock-barre-recherche">
          <Search size={18} color="#9CA3AF" />
          <input
            type="text"
            placeholder="Rechercher un produit...."
            value={recherche}
            onChange={(e) => setRecherche(e.target.value)}
          />
        </div>

        <div className="stock-tabs-zone">
          <div className="stock-tabs-ligne">
            <div className="stock-tabs">
              <button
                className={ongletActif === 'tout' ? 'stock-tab-actif' : 'stock-tab'}
                onClick={() => setOngletActif('tout')}
              >
                Tout
              </button>
              <button
                className={ongletActif === 'frigo' ? 'stock-tab-actif' : 'stock-tab'}
                onClick={() => setOngletActif('frigo')}
              >
                Frigo
              </button>
              <button
                className={ongletActif === 'placard' ? 'stock-tab-actif' : 'stock-tab'}
                onClick={() => setOngletActif('placard')}
              >
                Placard
              </button>
              <button
                className={ongletActif === 'autre' ? 'stock-tab-actif' : 'stock-tab'}
                onClick={() => setOngletActif('autre')}
              >
                Autre
              </button>
            </div>
            <button className="stock-bouton-ajout" onClick={() => onNaviguer('ajouterProduit')} aria-label="Ajouter un produit">
              <Plus size={20} color="#FFFFFF" />
            </button>
          </div>
          <div className="stock-tabs-fond" />
        </div>

        {chargement ? (
          <p style={{ color: '#fff' }}>Chargement...</p>
        ) : (
          <>
            {ongletActif === 'tout' && produitsBientot.length > 0 && (
              <div className="stock-section">
                <div className="stock-section-entete">
                  <h2 className="stock-section-titre">À consommer bientôt</h2>
                  <span className="stock-voir-tout" onClick={() => onNaviguer('stockBientot')}>
                    Voir tout →
                  </span>
                </div>
                {produitsBientot.map((produit) => (
                  <CarteProduitStock
                    key={produit.id}
                    produit={produit}
                    onAjuster={gererAjustement}
                    onOuvrirEdition={() => {}}
                    afficherQuantiteRestante={true}
                  />
                ))}
              </div>
            )}

            <div className="stock-section">
              <div className="stock-section-entete">
                <h2 className="stock-section-titre">{titreSection}</h2>
                <span className="stock-tri">
                  <ArrowUpDown size={14} color="#9CA3AF" /> Tri : Date
                </span>
              </div>
              {tousLesProduits.length === 0 ? (
                <p style={{ color: 'var(--couleur-texte-clair)' }}>Aucun produit.</p>
              ) : (
                tousLesProduits.map((produit) => (
                  <CarteProduitStock
                    key={produit.id}
                    produit={produit}
                    onAjuster={gererAjustement}
                    onOuvrirEdition={() => {}}
                  />
                ))
              )}
            </div>
          </>
        )}

      </div>

      {produitASupprimer && (
        <ModalSuppression
          titre="Supprimer ce produit ?"
          description={`"${produitASupprimer.nom}" est à 0. Voulez-vous le retirer de votre stock ?`}
          texteBouton="Supprimer"
          onConfirmer={confirmerSuppression}
          onFermer={() => setProduitASupprimer(null)}
        />
      )}

      <FooterNav pageActive="stock" onNaviguer={onNaviguer} />
    </div>
  );
}

export default StockScreen;