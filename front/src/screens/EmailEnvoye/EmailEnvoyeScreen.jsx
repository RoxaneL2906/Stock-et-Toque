import Header from '../../components/Header/Header';
import './EmailEnvoyeScreen.css';

function EmailEnvoyeScreen({ onNaviguer }) {
  return (
    <div className="ecran-complet">
      <Header />
      <div className="ecran-contenu">
        <h2 className="titre-vert">Mot de passe oublié</h2>

        <h3 className="titre-confirmation">Email envoyé !</h3>
        <p className="sous-titre">Vérifiez votre boîte mail !</p>

        <p className="lien-retour" onClick={() => onNaviguer('connexion')}>
          ← Retour à la connexion
        </p>
      </div>
    </div>
  );
}

export default EmailEnvoyeScreen;