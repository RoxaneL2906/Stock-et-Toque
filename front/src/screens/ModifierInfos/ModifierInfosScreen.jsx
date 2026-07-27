import { useState, useEffect } from 'react';
import { Camera, User, Info } from 'lucide-react';
import HeaderAppli from '../../components/HeaderAppli/HeaderAppli';
import FooterNav from '../../components/FooterNav/FooterNav';
import FormInput from '../../components/FormInput/FormInput';
import PrimaryButton from '../../components/PrimaryButton/PrimaryButton';
import { recupererProfil, modifierInformations, modifierEmail } from '../../services/profilApi';
import './ModifierInfosScreen.css';

function ModifierInfosScreen({ onNaviguer }) {
  const [profilInitial, setProfilInitial] = useState(null);
  const [prenom, setPrenom] = useState('');
  const [nom, setNom] = useState('');
  const [email, setEmail] = useState('');
  const [motDePasseActuel, setMotDePasseActuel] = useState('');
  const [erreur, setErreur] = useState('');
  const [succes, setSucces] = useState('');
  const [chargement, setChargement] = useState(false);

  useEffect(() => {
    recupererProfil().then((donnees) => {
      setProfilInitial(donnees);
      setPrenom(donnees.prenom);
      setNom(donnees.nom);
      setEmail(donnees.email);
    });
  }, []);

  if (!profilInitial) {
    return <p style={{ color: '#fff' }}>Chargement...</p>;
  }

  const initiales = `${profilInitial.prenom.charAt(0)}.${profilInitial.nom.charAt(0)}`;
  const prenomModifie = prenom !== profilInitial.prenom;
  const nomModifie = nom !== profilInitial.nom;
  const emailModifie = email !== profilInitial.email;

  const gererEnregistrement = async (e) => {
    e.preventDefault();
    setErreur('');
    setSucces('');
    setChargement(true);

    try {
      if (prenomModifie || nomModifie) {
        await modifierInformations({ prenom, nom });
      }
      if (emailModifie) {
        await modifierEmail({ nouvelEmail: email, motDePasseActuel });
        setSucces('Vos informations ont bien été mises à jour. Redirection...');
        setTimeout(() => onNaviguer('connexion'), 2000);
      } else {
        setSucces('Vos informations ont bien été mises à jour.');
        setProfilInitial({ ...profilInitial, prenom, nom, email });
      }
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
        <h2 className="titre-ecran">Modifier mes informations personnelles</h2>

        <div className="profil-avatar-zone">
          <div className="profil-avatar">
            <span>{initiales}</span>
            <button className="profil-avatar-camera" aria-label="Changer la photo de profil">
              <Camera size={14} color="#111827" />
            </button>
          </div>
        </div>

        <form onSubmit={gererEnregistrement} className="cadre-formulaire">
          <User size={20} color="#F97316" className="cadre-icone" />

          <FormInput label="Prénom" value={prenom} onChange={setPrenom} modifie={prenomModifie} />

          <FormInput label="Nom" value={nom} onChange={setNom} modifie={nomModifie} />

          <FormInput label="Email" value={email} onChange={setEmail} modifie={emailModifie} />

          {emailModifie && (
            <div className="champ-mot-de-passe">
              <p className="texte-info">
                <Info size={14} color="#F97316" />
                <span>Mot de passe requis pour confirmer le changement d'email</span>
              </p>
              <FormInput
                label="Mot de passe actuel"
                value={motDePasseActuel}
                onChange={setMotDePasseActuel}
                type="password"
              />
            </div>
          )}

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

export default ModifierInfosScreen;