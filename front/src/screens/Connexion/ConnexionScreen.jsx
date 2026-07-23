import { useState } from 'react';
import Header from '../../components/Header/Header';
import FormInput from '../../components/FormInput/FormInput';
import PrimaryButton from '../../components/PrimaryButton/PrimaryButton';
import { connecter } from '../../services/authApi';
import './ConnexionScreen.css';

function ConnexionScreen({ onNaviguer }) {
  const [email, setEmail] = useState('');
  const [motDePasse, setMotDePasse] = useState('');
  const [seSouvenirDeMoi, setSeSouvenirDeMoi] = useState(false);
  const [erreur, setErreur] = useState('');
  const [chargement, setChargement] = useState(false);

  const gererSoumission = async (e) => {
    e.preventDefault();
    setErreur('');
    setChargement(true);

    try {
      await connecter({ email, motDePasse, seSouvenirDeMoi });
      onNaviguer('accueil');
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
        <h2 className="titre-vert">Connexion</h2>

        <form onSubmit={gererSoumission} className="formulaire">
          <FormInput label="Email" value={email} onChange={setEmail} />

          <div className="ligne-mot-de-passe">
            <label className="form-input-label">Mot de passe</label>
            <span className="lien-oublie" onClick={() => onNaviguer('motDePasseOublie')}>Oublié ?</span>
          </div>
          <FormInput value={motDePasse} onChange={setMotDePasse} type="password" />

          <label className="ligne-souvenir">
            <input
              type="checkbox"
              checked={seSouvenirDeMoi}
              onChange={(e) => setSeSouvenirDeMoi(e.target.checked)}
            />
            <span>Se souvenir de moi</span>
          </label>

          {erreur && <p className="message-erreur">{erreur}</p>}

          <p className="bienvenue">Bienvenue !</p>

          <PrimaryButton
            texte={chargement ? 'Connexion...' : 'Se connecter →'}
            type="submit"
          />
        </form>

        <p className="ligne-lien">
          Nouveau ?{' '}
          <span className="lien-vert" onClick={() => onNaviguer('inscription')}>Créer un compte</span>
        </p>
      </div>
    </div>
  );
}

export default ConnexionScreen;