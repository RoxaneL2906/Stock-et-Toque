import { useState } from 'react';
import { KeyRound } from 'lucide-react';
import HeaderAppli from '../../components/HeaderAppli/HeaderAppli';
import FooterNav from '../../components/FooterNav/FooterNav';
import FormInput from '../../components/FormInput/FormInput';
import PrimaryButton from '../../components/PrimaryButton/PrimaryButton';
import { modifierMotDePasse } from '../../services/profilApi';
import './ChangerMotDePasseScreen.css';

function ChangerMotDePasseScreen({ onNaviguer }) {
  const [motDePasseActuel, setMotDePasseActuel] = useState('');
  const [nouveauMotDePasse, setNouveauMotDePasse] = useState('');
  const [confirmationNouveauMotDePasse, setConfirmationNouveauMotDePasse] = useState('');
  const [erreur, setErreur] = useState('');
  const [succes, setSucces] = useState('');
  const [chargement, setChargement] = useState(false);

  const gererSoumission = async (e) => {
    e.preventDefault();
    setErreur('');
    setSucces('');
    setChargement(true);

    try {
      await modifierMotDePasse({
        motDePasseActuel,
        nouveauMotDePasse,
        confirmationNouveauMotDePasse,
      });
      setSucces('Votre mot de passe a bien été modifié. Un email de confirmation vous a été envoyé. Redirection...');
      setTimeout(() => onNaviguer('connexion'), 2000);
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
        <h2 className="titre-ecran">Changer le mot de passe</h2>

        <p className="texte-info-centre">
          Un mail de confirmation vous sera envoyé après validation
        </p>

        <form onSubmit={gererSoumission} className="cadre-formulaire">
          <KeyRound size={20} color="#F97316" className="cadre-icone" />

          <FormInput
            label="Mot de passe actuel"
            value={motDePasseActuel}
            onChange={setMotDePasseActuel}
            type="password"
          />

          <FormInput
            label="Nouveau mot de passe"
            value={nouveauMotDePasse}
            onChange={setNouveauMotDePasse}
            type="password"
          />

          <FormInput
            label="Confirmer le mot de passe"
            value={confirmationNouveauMotDePasse}
            onChange={setConfirmationNouveauMotDePasse}
            type="password"
          />

          {erreur && <p className="message-erreur">{erreur}</p>}
          {succes && <p className="message-succes">{succes}</p>}

          <PrimaryButton texte={chargement ? 'Enregistrement...' : 'Enregistrer'} type="submit" />
        </form>

        <p className="lien-retour" onClick={() => onNaviguer('profil')}>← Retour au profil</p>
      </div>
      <FooterNav pageActive="profil" onNaviguer={onNaviguer} />
    </div>
  );
}

export default ChangerMotDePasseScreen;