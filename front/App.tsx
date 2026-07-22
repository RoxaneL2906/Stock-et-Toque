import { useState } from 'react';
import { StatusBar } from 'react-native';
import { SafeAreaProvider } from 'react-native-safe-area-context';
import AccueilScreen from './src/screens/Accueil/AccueilScreen';
import InscriptionScreen from './src/screens/Inscription/InscriptionScreen';
import ConnexionScreen from './src/screens/Connexion/ConnexionScreen';

type Ecran = 'accueil' | 'inscription' | 'connexion';

function App() {
  const [ecranActuel, setEcranActuel] = useState<Ecran>('accueil');

  return (
    <SafeAreaProvider>
      <StatusBar barStyle="light-content" />
      {ecranActuel === 'accueil' && (
        <AccueilScreen onNaviguer={setEcranActuel} />
      )}
      {ecranActuel === 'inscription' && (
        <InscriptionScreen onNaviguer={setEcranActuel} />
      )}
      {ecranActuel === 'connexion' && (
        <ConnexionScreen onNaviguer={setEcranActuel} />
      )}
    </SafeAreaProvider>
  );
}

export default App;