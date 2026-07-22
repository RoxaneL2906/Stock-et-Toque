import { useState } from 'react';
import { ScrollView, Text, TouchableOpacity, View } from 'react-native';
import Header from '../../components/Header/Header';
import FormInput from '../../components/FormInput/FormInput';
import PrimaryButton from '../../components/PrimaryButton/PrimaryButton';
import { styles } from './ConnexionScreen.styles';

type Props = {
  onNaviguer: (ecran: 'accueil' | 'inscription' | 'connexion') => void;
};

function ConnexionScreen({ onNaviguer }: Props) {
  const [email, setEmail] = useState('');
  const [motDePasse, setMotDePasse] = useState('');
  const [seSouvenirDeMoi, setSeSouvenirDeMoi] = useState(false);

  return (
    <View style={styles.ecranComplet}>
      <Header />

      <ScrollView style={styles.container} contentContainerStyle={styles.contenu}>
        <Text style={styles.titre}>Connexion</Text>

        <FormInput label="Email" value={email} onChangeText={setEmail} />

        <View style={styles.ligneMotDePasse}>
          <Text style={styles.label}>Mot de passe</Text>
          <TouchableOpacity>
            <Text style={styles.lienOublie}>Oublié ?</Text>
          </TouchableOpacity>
        </View>
        <FormInput
          label=""
          value={motDePasse}
          onChangeText={setMotDePasse}
          secureTextEntry
        />

        <TouchableOpacity
          style={styles.ligneSouvenir}
          onPress={() => setSeSouvenirDeMoi(!seSouvenirDeMoi)}
        >
          <View style={[styles.checkbox, seSouvenirDeMoi && styles.checkboxCoche]} />
          <Text style={styles.texteSimple}>Se souvenir de moi</Text>
        </TouchableOpacity>

        <Text style={styles.bienvenue}>Bienvenue !</Text>

        <PrimaryButton texte="Se connecter →" />

        <View style={styles.ligneInscription}>
          <Text style={styles.texteSimple}>Nouveau ? </Text>
          <TouchableOpacity onPress={() => onNaviguer('inscription')}>
            <Text style={styles.lienVert}>Créer un compte</Text>
          </TouchableOpacity>
        </View>
      </ScrollView>
    </View>
  );
}

export default ConnexionScreen;