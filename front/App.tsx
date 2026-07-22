import { StatusBar } from 'react-native';
import { SafeAreaProvider } from 'react-native-safe-area-context';
import AccueilScreen from './src/screens/Accueil/AccueilScreen';

function App() {
  return (
    <SafeAreaProvider>
      <StatusBar barStyle="light-content" />
      <AccueilScreen />
    </SafeAreaProvider>
  );
}

export default App;