import { useState } from 'react';
import Header from '../../components/Header/Header';
import FormInput from '../../components/FormInput/FormInput';
import PrimaryButton from '../../components/PrimaryButton/PrimaryButton';
import { demanderReinitialisation } from '../../services/authApi';
import './MotDePasseOublieScreen.css';

function MotDePasseOublieScreen({ onNaviguer }) {
  const [email, setEmail] = useState('');
  const [erreur, setErreur] = useState('');
  const [chargement, setChargement] = useState(false);

  const gererSoumission = async (e) => {
    e.preventDefault();
    setErreur('');
    setChargement(true);
    try {
      await demanderReinitialisation(email);
      onNaviguer('emailEnvoye');
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
        <h2 className="titre-vert">Mot de passe oublié</h2>
        <p className="sous-titre">
          Entrez votre adresse mail pour recevoir un lien de réinitialisation
        </p>

        <form onSubmit={gererSoumission} className="formulaire">
          <FormInput label="Email" value={email} onChange={setEmail} />

          {erreur && <p className="message-erreur">{erreur}</p>}

          <PrimaryButton
            texte={chargement ? 'Envoi...' : 'Recevoir un mail'}
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

export default MotDePasseOublieScreen;