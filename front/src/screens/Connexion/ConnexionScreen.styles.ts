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
  ligneMotDePasse: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    width: '100%',
    marginBottom: 6,
  },
  label: {
    fontSize: 13,
    color: '#FFFFFF',
  },
  lienOublie: {
    fontSize: 13,
    color: '#F97316',
  },
  ligneSouvenir: {
    flexDirection: 'row',
    alignItems: 'center',
    width: '100%',
    marginTop: 8,
    marginBottom: 20,
  },
  checkbox: {
    width: 18,
    height: 18,
    borderWidth: 1,
    borderColor: '#F97316',
    borderRadius: 3,
    marginRight: 8,
  },
  checkboxCoche: {
    backgroundColor: '#F97316',
  },
  bienvenue: {
    fontSize: 14,
    color: '#D9D9D9',
    marginBottom: 20,
  },
  ligneInscription: {
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