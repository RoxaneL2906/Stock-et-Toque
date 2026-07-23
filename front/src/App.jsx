import { useState, useEffect } from 'react';
import { useDeconnexionAutomatique } from './hooks/useDeconnexionAutomatique';
import AccueilScreen from './screens/Accueil/AccueilScreen';
import InscriptionScreen from './screens/Inscription/InscriptionScreen';
import ConnexionScreen from './screens/Connexion/ConnexionScreen';
import MotDePasseOublieScreen from './screens/MotDePasseOublie/MotDePasseOublieScreen';
import EmailEnvoyeScreen from './screens/EmailEnvoye/EmailEnvoyeScreen';
import ReinitialiserMotDePasseScreen from './screens/ReinitialiserMotDePasse/ReinitialiserMotDePasseScreen';

function App() {
  const [ecranActuel, setEcranActuel] = useState('accueil');

  useEffect(() => {
    const parametres = new URLSearchParams(window.location.search);
    if (parametres.get('token')) {
      setEcranActuel('reinitialiserMotDePasse');
    }
  }, []);

  useDeconnexionAutomatique(() => setEcranActuel('connexion'));

  return (
    <>
      {ecranActuel === 'accueil' && <AccueilScreen onNaviguer={setEcranActuel} />}
      {ecranActuel === 'inscription' && <InscriptionScreen onNaviguer={setEcranActuel} />}
      {ecranActuel === 'connexion' && <ConnexionScreen onNaviguer={setEcranActuel} />}
      {ecranActuel === 'motDePasseOublie' && <MotDePasseOublieScreen onNaviguer={setEcranActuel} />}
      {ecranActuel === 'emailEnvoye' && <EmailEnvoyeScreen onNaviguer={setEcranActuel} />}
      {ecranActuel === 'reinitialiserMotDePasse' && <ReinitialiserMotDePasseScreen onNaviguer={setEcranActuel} />}
    </>
  );
}

export default App;