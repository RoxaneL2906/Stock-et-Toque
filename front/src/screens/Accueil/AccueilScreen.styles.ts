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
  description: {
    fontSize: 14,
    color: '#FFFFFF',
    textAlign: 'center',
    marginTop: 16,
    marginBottom: 20,
  },
  boutonsColonne: {
    alignItems: 'center',
    marginBottom: 24,
    gap: 12,
  },
  cardsGrid: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    justifyContent: 'space-between',
    width: '100%',
  },
});