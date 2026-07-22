import { useState } from 'react';
import Header from '../../components/Header/Header';
import FormInput from '../../components/FormInput/FormInput';
import PrimaryButton from '../../components/PrimaryButton/PrimaryButton';
import './ConnexionScreen.css';

function ConnexionScreen({ onNaviguer }) {
  const [email, setEmail] = useState('');
  const [motDePasse, setMotDePasse] = useState('');
  const [seSouvenirDeMoi, setSeSouvenirDeMoi] = useState(false);

  return (
    <div className="ecran-complet">
      <Header />
      <div className="ecran-contenu">
        <h2 className="titre-vert">Connexion</h2>

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

        <p className="bienvenue">Bienvenue !</p>

        <PrimaryButton texte="Se connecter →" />

        <p className="ligne-lien">
          Nouveau ?{' '}
          <span className="lien-vert" onClick={() => onNaviguer('inscription')}>Créer un compte</span>
        </p>
      </div>
    </div>
  );
}

export default ConnexionScreen;