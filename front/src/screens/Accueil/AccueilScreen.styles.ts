import { StyleSheet } from 'react-native';

export const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#111827',
    width: '100%',
    maxWidth: 480,
    alignSelf: 'center',
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
  },
  bouton: {
    backgroundColor: '#22C55E',
    borderRadius: 20,
    paddingVertical: 10,
    paddingHorizontal: 18,
    marginVertical: 6,
    width: 200,
    alignItems: 'center',
  },
  boutonTexte: {
    color: '#000000',
    fontWeight: 'bold',
    fontSize: 13,
  },
  cardsGrid: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    justifyContent: 'space-between',
    width: '100%',
  },
});