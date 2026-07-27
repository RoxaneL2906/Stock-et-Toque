// Inspiré du modal de suppression fait pour le projet MarsAI du groupe 1 Toulouse (projet de stage)
import { useState } from 'react';
import { Trash2 } from 'lucide-react';
import FormInput from '../FormInput/FormInput';
import PrimaryButton from '../PrimaryButton/PrimaryButton';
import './ModalSuppression.css';

function ModalSuppression({
  titre,
  description,
  texteBouton = 'Supprimer',
  avecMotDePasse = false,
  onConfirmer,
  onFermer,
}) {
  const [motDePasse, setMotDePasse] = useState('');
  const [erreur, setErreur] = useState('');
  const [chargement, setChargement] = useState(false);

  const gererConfirmation = async () => {
    setErreur('');
    setChargement(true);

    try {
      await onConfirmer(motDePasse);
    } catch (err) {
      setErreur(err.message);
    } finally {
      setChargement(false);
    }
  };

  return (
    <div className="modal-overlay-conteneur">
      <div className="modal-fond" onClick={onFermer} />
      <div className="modal-carte">
        <div className="modal-icone-badge">
          <Trash2 size={28} color="#EF4444" />
        </div>
        <h3 className="modal-titre">{titre}</h3>
        <p className="modal-description">{description}</p>

        {avecMotDePasse && (
          <div className="modal-champ-mot-de-passe">
            <FormInput
              label="Mot de passe"
              value={motDePasse}
              onChange={setMotDePasse}
              type="password"
            />
          </div>
        )}

        {erreur && <p className="modal-erreur">{erreur}</p>}

        <div className="modal-boutons">
          <button className="modal-bouton-annuler" onClick={onFermer}>
            Annuler
          </button>
          <PrimaryButton
            texte={chargement ? 'Suppression...' : texteBouton}
            onClick={gererConfirmation}
          />
        </div>
      </div>
    </div>
  );
}

export default ModalSuppression;