import { useState } from 'react';
import AccueilScreen from './screens/Accueil/AccueilScreen';
import InscriptionScreen from './screens/Inscription/InscriptionScreen';
import ConnexionScreen from './screens/Connexion/ConnexionScreen';
import MotDePasseOublieScreen from './screens/MotDePasseOublie/MotDePasseOublieScreen';
import EmailEnvoyeScreen from './screens/EmailEnvoye/EmailEnvoyeScreen';

function App() {
  const [ecranActuel, setEcranActuel] = useState('accueil');

  return (
    <>
      {ecranActuel === 'accueil' && <AccueilScreen onNaviguer={setEcranActuel} />}
      {ecranActuel === 'inscription' && <InscriptionScreen onNaviguer={setEcranActuel} />}
      {ecranActuel === 'connexion' && <ConnexionScreen onNaviguer={setEcranActuel} />}
      {ecranActuel === 'motDePasseOublie' && <MotDePasseOublieScreen onNaviguer={setEcranActuel} />}
      {ecranActuel === 'emailEnvoye' && <EmailEnvoyeScreen onNaviguer={setEcranActuel} />}
    </>
  );
}

export default App;