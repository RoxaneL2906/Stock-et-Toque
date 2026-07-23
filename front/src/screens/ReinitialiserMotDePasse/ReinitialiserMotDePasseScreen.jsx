import { useState } from 'react';
import Header from '../../components/Header/Header';
import FormInput from '../../components/FormInput/FormInput';
import PrimaryButton from '../../components/PrimaryButton/PrimaryButton';
import { reinitialiserMotDePasse } from '../../services/authApi';
import './ReinitialiserMotDePasseScreen.css';

function ReinitialiserMotDePasseScreen({ onNaviguer }) {
  const [nouveauMotDePasse, setNouveauMotDePasse] = useState('');
  const [confirmationNouveauMotDePasse, setConfirmationNouveauMotDePasse] = useState('');
  const [erreur, setErreur] = useState('');
  const [chargement, setChargement] = useState(false);

  const gererSoumission = async (e) => {
    e.preventDefault();
    setErreur('');
    setChargement(true);

    const parametres = new URLSearchParams(window.location.search);
    const token = parametres.get('token');

    try {
      await reinitialiserMotDePasse({
        token,
        nouveauMotDePasse,
        confirmationNouveauMotDePasse,
      });
      onNaviguer('connexion');
    } catch (err) {
      setErreur(err.message);
    } finally {
      setChargement(false);
    }
  };

  return (
    <div className="ecran-complet">
      <Header />
      <div className="ecran-contenu">
        <h2 className="titre-vert">Réinitialiser le mot de passe</h2>
        <p className="sous-titre">
          Choisissez un nouveau mot de passe pour votre compte
        </p>

        <form onSubmit={gererSoumission} className="formulaire">
          <FormInput
            label="Nouveau mot de passe"
            value={nouveauMotDePasse}
            onChange={setNouveauMotDePasse}
            type="password"
          />
          <p className="texte-aide">
            <span className="icone-info">ⓘ </span>
            Le mot de passe doit contenir 8 caractères avec 1 majuscule,
            1 minuscule, 1 chiffre et 1 caractère spécial
          </p>

          <FormInput
            label="Confirmer le mot de passe"
            value={confirmationNouveauMotDePasse}
            onChange={setConfirmationNouveauMotDePasse}
            type="password"
          />

          {erreur && <p className="message-erreur">{erreur}</p>}

          <PrimaryButton
            texte={chargement ? 'Envoi...' : 'Réinitialiser →'}
            type="submit"
          />
        </form>

        <p className="lien-retour" onClick={() => onNaviguer('connexion')}>
          ← Retour à la connexion
        </p>
      </div>
    </div>
  );
}

export default ReinitialiserMotDePasseScreen;