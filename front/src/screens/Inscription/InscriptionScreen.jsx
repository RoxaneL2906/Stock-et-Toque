import { useState } from 'react';
import Header from '../../components/Header/Header';
import FormInput from '../../components/FormInput/FormInput';
import PrimaryButton from '../../components/PrimaryButton/PrimaryButton';
import { inscrire } from '../../services/authApi';
import './InscriptionScreen.css';

function InscriptionScreen({ onNaviguer }) {
  const [prenom, setPrenom] = useState('');
  const [nom, setNom] = useState('');
  const [email, setEmail] = useState('');
  const [motDePasse, setMotDePasse] = useState('');
  const [confirmationMotDePasse, setConfirmationMotDePasse] = useState('');
  const [erreur, setErreur] = useState('');
  const [chargement, setChargement] = useState(false);

  const gererSoumission = async (e) => {
    e.preventDefault();
    setErreur('');
    setChargement(true);

    try {
      await inscrire({ prenom, nom, email, motDePasse, confirmationMotDePasse });
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
        <h2 className="titre-vert">Créer un compte</h2>

        <form onSubmit={gererSoumission} className="formulaire">
          <FormInput label="Prénom" value={prenom} onChange={setPrenom} />
          <FormInput label="Nom" value={nom} onChange={setNom} />
          <FormInput label="Email" value={email} onChange={setEmail} />

          <FormInput label="Mot de passe" value={motDePasse} onChange={setMotDePasse} type="password" />
          <p className="texte-aide">
            <span className="icone-info">ⓘ </span>
            Le mot de passe doit contenir 8 caractères avec 1 majuscule,
            1 minuscule, 1 chiffre et 1 caractère spécial
          </p>

          <FormInput label="Confirmer mot de passe" value={confirmationMotDePasse} onChange={setConfirmationMotDePasse} type="password" />

          {erreur && <p className="message-erreur">{erreur}</p>}

          <PrimaryButton
            texte={chargement ? 'Inscription...' : "S'inscrire →"}
            type="submit"
          />
        </form>

        <p className="ligne-lien">
          Déjà un compte ?{' '}
          <span className="lien-vert" onClick={() => onNaviguer('connexion')}>Se connecter</span>
        </p>
      </div>
    </div>
  );
}

export default InscriptionScreen;