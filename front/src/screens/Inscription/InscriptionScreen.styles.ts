import { StyleSheet } from 'react-native';

export const styles = StyleSheet.create({
  ecranComplet: {
    flex: 1,
    backgroundColor: '#111827',
    width: '100%',
    maxWidth: 480,
    alignSelf: 'center',
  },
  container: {
    flex: 1,
  },
  contenu: {
    padding: 20,
    alignItems: 'center',
  },
  titre: {
    fontSize: 18,
    fontWeight: 'bold',
    color: '#22C55E',
    marginTop: 20,
    marginBottom: 20,
  },
  aide: {
    fontSize: 11,
    color: '#9CA3AF',
    marginTop: -10,
    marginBottom: 16,
    alignSelf: 'flex-start',
  },
  icone: {
    color: '#60A5FA',
  },
  ligneConnexion: {
    flexDirection: 'row',
    marginTop: 16,
  },
  texteSimple: {
    fontSize: 13,
    color: '#D9D9D9',
  },
  lienVert: {
    fontSize: 13,
    color: '#22C55E',
    fontWeight: 'bold',
  },
});