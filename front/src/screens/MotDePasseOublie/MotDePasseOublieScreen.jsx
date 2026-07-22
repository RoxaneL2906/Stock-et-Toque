import { useState } from 'react';
import Header from '../../components/Header/Header';
import FormInput from '../../components/FormInput/FormInput';
import PrimaryButton from '../../components/PrimaryButton/PrimaryButton';
import './MotDePasseOublieScreen.css';

function MotDePasseOublieScreen({ onNaviguer }) {
  const [email, setEmail] = useState('');

  return (
    <div className="ecran-complet">
      <Header />
      <div className="ecran-contenu">
        <h2 className="titre-vert">Mot de passe oublié</h2>
        <p className="sous-titre">
          Entrez votre adresse mail pour recevoir un lien de réinitialisation
        </p>

        <FormInput label="Email" value={email} onChange={setEmail} />

        <PrimaryButton texte="Recevoir un mail" onClick={() => onNaviguer('emailEnvoye')} />

        <p className="lien-retour" onClick={() => onNaviguer('connexion')}>
          ← Retour à la connexion
        </p>
      </div>
    </div>
  );
}

export default MotDePasseOublieScreen;