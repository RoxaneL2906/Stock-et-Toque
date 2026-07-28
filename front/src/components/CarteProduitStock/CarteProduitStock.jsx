import { Minus, Plus } from 'lucide-react';
import './CarteProduitStock.css';

function formaterTexteExpiration(dlc, ddm) {
  const date = dlc || ddm;
  if (!date) return null;

  const aujourdhui = new Date();
  aujourdhui.setHours(0, 0, 0, 0);
  const dateExpiration = new Date(date);
  const joursRestants = Math.round((dateExpiration - aujourdhui) / (1000 * 60 * 60 * 24));

  if (joursRestants < 0) return 'Expiré';
  if (joursRestants === 0) return 'Expire aujourd\'hui !';
  if (joursRestants === 1) return 'Expire demain !';
  if (joursRestants <= 7) return `Expire dans ${joursRestants} jours`;

  const jour = String(dateExpiration.getDate()).padStart(2, '0');
  const mois = dateExpiration.toLocaleDateString('fr-FR', { month: 'long' });
  return `Exp. le ${jour} ${mois}`;
}

function couleurSelonAlerte(alerte) {
  if (alerte === 'rouge') return '#EF4444';
  if (alerte === 'orange') return '#F97316';
  return 'var(--couleur-vert)';
}

function formaterSousLibelleUnite(unite, quantite, nom) {
  if (!unite) return null;
  if (unite === 'unite') return `x${quantite} ${nom}`;
  if (unite === 'kg') return `${quantite} Kg`;
  if (unite === 'l') return `${quantite} L`;
  if (unite === 'pack') return `${quantite} Pack${quantite > 1 ? 's' : ''}`;
  return null;
}

function CarteProduitStock({ produit, onAjuster, onOuvrirEdition, afficherQuantiteRestante = false }) {
  const texteExpiration = formaterTexteExpiration(produit.dlc, produit.ddm);
  const couleurTexte = couleurSelonAlerte(produit.alerte);
  const sousLibelleUnite = formaterSousLibelleUnite(produit.unite, produit.quantite, produit.nom);

  return (
    <div className="carte-produit-stock">
      <div className="carte-produit-image">
        {produit.photo ? (
          <img src={produit.photo} alt={produit.nom} />
        ) : (
          <div className="carte-produit-image-defaut" />
        )}
      </div>

      <div className="carte-produit-infos" onClick={() => onOuvrirEdition(produit)}>
        <p className="carte-produit-nom">{produit.nom}</p>
        {texteExpiration && (
          <p className="carte-produit-expiration" style={{ color: couleurTexte }}>
            {texteExpiration}
          </p>
        )}
        {afficherQuantiteRestante ? (
          <p className="carte-produit-sous-label">Quantité restante</p>
        ) : (
          sousLibelleUnite && <p className="carte-produit-sous-label">{sousLibelleUnite}</p>
        )}
      </div>

      <div className="carte-produit-quantite">
        <button onClick={() => onAjuster(produit.id, -1)} aria-label="Diminuer la quantité">
          <Minus size={14} color="#FFFFFF" />
        </button>
        <span>{produit.quantite}</span>
        <button onClick={() => onAjuster(produit.id, 1)} aria-label="Augmenter la quantité">
          <Plus size={14} color="#FFFFFF" />
        </button>
      </div>
    </div>
  );
}

export default CarteProduitStock;