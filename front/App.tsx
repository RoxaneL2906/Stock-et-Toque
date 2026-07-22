import { StatusBar, StyleSheet, View } from 'react-native';
import { SafeAreaProvider } from 'react-native-safe-area-context';
import Header from './src/components/Header/Header';

function App() {
  return (
    <SafeAreaProvider>
      <StatusBar barStyle="light-content" />
      <View style={styles.container}>
        <Header />
      </View>
    </SafeAreaProvider>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#111827',
  },
});

export default App;