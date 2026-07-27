import { useState, useEffect } from 'react';
import { Camera, User, KeyRound, Utensils, Bell, Moon, X, BookMarked, Heart } from 'lucide-react';
import HeaderAppli from '../../components/HeaderAppli/HeaderAppli';
import FooterNav from '../../components/FooterNav/FooterNav';
import PrimaryButton from '../../components/PrimaryButton/PrimaryButton';
import NavigationChevron from '../../components/NavigationChevron/NavigationChevron';
import ToggleSwitch from '../../components/ToggleSwitch/ToggleSwitch';
import ModalSuppression from '../../components/ModalSuppression/ModalSuppression';
import { recupererProfil, supprimerCompte } from '../../services/profilApi';
import './ProfilScreen.css';

function ProfilScreen({ onNaviguer }) {
  const [notificationsActives, setNotificationsActives] = useState(true);
  const [modeNuitActif, setModeNuitActif] = useState(true);
  const [modalSuppressionOuverte, setModalSuppressionOuverte] = useState(false);

  const [utilisateur, setUtilisateur] = useState(null);
  const [erreur, setErreur] = useState('');

  useEffect(() => {
    recupererProfil()
      .then((donnees) => setUtilisateur(donnees))
      .catch((err) => setErreur(err.message));
  }, []);

  if (erreur) {
    return <p style={{ color: '#EF4444' }}>{erreur}</p>;
  }

  if (!utilisateur) {
    return <p style={{ color: '#fff' }}>Chargement...</p>;
  }

  const initiales = `${utilisateur.prenom.charAt(0)}.${utilisateur.nom.charAt(0)}`;

  return (
    <div className="ecran-complet">
      <HeaderAppli />
      <div className="ecran-contenu">

        <div className="profil-avatar-zone">
          <div className="profil-avatar">
            <span>{initiales}</span>
            <button className="profil-avatar-camera" aria-label="Changer la photo de profil">
              <Camera size={14} color="#111827" />
            </button>
          </div>
          <p className="profil-nom">{utilisateur.prenom} {utilisateur.nom}</p>
          <p className="profil-email">{utilisateur.email}</p>
        </div>

        <div className="profil-section">
          <h2 className="profil-section-titre">Informations personnelles</h2>
          <NavigationChevron Icone={User} couleur="#F97316" texte="Modifier mes informations personnelles" onClick={() => onNaviguer('modifierInfos')} />
          <NavigationChevron Icone={KeyRound} couleur="#F97316" texte="Changer le mot de passe" onClick={() => onNaviguer('changerMotDePasse')} />
        </div>

        <div className="profil-section">
          <h2 className="profil-section-titre">Profil culinaire</h2>
          <NavigationChevron Icone={Utensils} couleur="#22C55E" texte="Compléter ou modifier mes préférences" onClick={() => onNaviguer('preferences')} />
        </div>

        <div className="profil-cartes-grid">
          <button className="profil-carte" onClick={() => onNaviguer('mesRecettes')}>
            <BookMarked size={18} color="#22C55E" />
            <span>Mes recettes</span>
          </button>
          <button className="profil-carte" onClick={() => onNaviguer('mesFavoris')}>
            <Heart size={18} color="#EF4444" />
            <span>Mes favoris</span>
          </button>
        </div>

        <div className="profil-section">
          <h2 className="profil-section-titre">Réglages</h2>
          <ToggleSwitch Icone={Bell} couleur="#EF4444" texte="Notifications" active={notificationsActives} onChange={() => setNotificationsActives(!notificationsActives)} />
          <ToggleSwitch Icone={Moon} couleur="#EF4444" texte="Mode nuit" active={modeNuitActif} onChange={() => setModeNuitActif(!modeNuitActif)} />
          <button className="profil-supprimer-compte" onClick={() => setModalSuppressionOuverte(true)}>
            <X size={16} color="#EF4444" />
            <span>Supprimer mon compte</span>
          </button>
        </div>

        <PrimaryButton texte="Déconnexion" onClick={() => onNaviguer('connexion')} />

      </div>

      {modalSuppressionOuverte && (
        <ModalSuppression
          titre="Supprimer mon compte"
          description="Voulez-vous vraiment supprimer votre compte ? Cette action est irréversible : toutes vos données personnelles seront supprimées."
          avecMotDePasse={true}
          onConfirmer={async (motDePasse) => {
            await supprimerCompte({ motDePasse });
            onNaviguer('connexion');
          }}
          onFermer={() => setModalSuppressionOuverte(false)}
        />
      )}

      <FooterNav pageActive="profil" onNaviguer={onNaviguer} />
    </div>
  );
}

export default ProfilScreen;