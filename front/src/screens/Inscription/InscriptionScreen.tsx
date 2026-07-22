import { useState } from 'react';
import { ScrollView, Text, TouchableOpacity, View } from 'react-native';
import Header from '../../components/Header/Header';
import FormInput from '../../components/FormInput/FormInput';
import PrimaryButton from '../../components/PrimaryButton/PrimaryButton';
import { styles } from './InscriptionScreen.styles';

type Props = {
  onNaviguer: (ecran: 'accueil' | 'inscription' | 'connexion') => void;
};

function InscriptionScreen({ onNaviguer }: Props) {
  const [prenom, setPrenom] = useState('');
  const [nom, setNom] = useState('');
  const [email, setEmail] = useState('');
  const [motDePasse, setMotDePasse] = useState('');
  const [confirmationMotDePasse, setConfirmationMotDePasse] = useState('');

  return (
    <View style={styles.ecranComplet}>
      <Header />

      <ScrollView style={styles.container} contentContainerStyle={styles.contenu}>
        <Text style={styles.titre}>Créer un compte</Text>

        <FormInput label="Prénom" value={prenom} onChangeText={setPrenom} />
        <FormInput label="Nom" value={nom} onChangeText={setNom} />
        <FormInput label="Email" value={email} onChangeText={setEmail} />

        <FormInput
          label="Mot de passe"
          value={motDePasse}
          onChangeText={setMotDePasse}
          secureTextEntry
        />
        <Text style={styles.aide}>
          <Text style={styles.icone}>ⓘ </Text>
          Le mot de passe doit contenir 8 caractères avec 1 majuscule,
          1 minuscule, 1 chiffre et 1 caractère spécial
        </Text>

        <FormInput
          label="Confirmer mot de passe"
          value={confirmationMotDePasse}
          onChangeText={setConfirmationMotDePasse}
          secureTextEntry
        />

        <PrimaryButton texte="S'inscrire →" />

        <View style={styles.ligneConnexion}>
          <Text style={styles.texteSimple}>Déjà un compte ? </Text>
          <TouchableOpacity onPress={() => onNaviguer('connexion')}>
            <Text style={styles.lienVert}>Se connecter</Text>
          </TouchableOpacity>
        </View>
      </ScrollView>
    </View>
  );
}

export default InscriptionScreen;