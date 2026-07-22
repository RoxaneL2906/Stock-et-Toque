import { ScrollView, Text, TouchableOpacity, View } from 'react-native';
import Header from '../../components/Header/Header';
import FeatureCard from '../../components/FeatureCard/FeatureCard';
import { styles } from './AccueilScreen.styles';

function AccueilScreen() {
  return (
    <ScrollView style={styles.container} contentContainerStyle={styles.contenu}>
      <Header />

      <Text style={styles.description}>
        Stock & Toque permet à l'utilisateur de gérer son stock de produits,
        planifier ses repas, organiser ses courses et accéder à des recettes
        filtrées selon ses préférences, son régime alimentaire et ses
        allergies.
      </Text>

      <View style={styles.boutonsColonne}>
        <TouchableOpacity style={styles.bouton}>
          <Text style={styles.boutonTexte}>En savoir plus →</Text>
        </TouchableOpacity>
        <TouchableOpacity style={styles.bouton}>
          <Text style={styles.boutonTexte}>Se connecter →</Text>
        </TouchableOpacity>
      </View>

      <View style={styles.cardsGrid}>
        <FeatureCard
          icone={require('../../assets/images/icone-recettes.png')}
          titre="Recettes"
          description={'Privées & publiques\nFiltrées par préférences'}
          couleur="#22C55E"
        />
        <FeatureCard
          icone={require('../../assets/images/icone-planning.png')}
          titre="Planning"
          description={'7 jours, midi & soir\nSuggestions'}
          couleur="#F97316"
        />
        <FeatureCard
          icone={require('../../assets/images/icone-stock.png')}
          titre="Stock"
          description={'Frigo & Placard\nAlertes DLC/DDM'}
          couleur="#60A5FA"
        />
        <FeatureCard
          icone={require('../../assets/images/icone-courses.png')}
          titre="Courses"
          description={'Manuelle ou auto\nDepuis les recettes'}
          couleur="#A78BFA"
        />
      </View>

      <TouchableOpacity style={styles.bouton}>
        <Text style={styles.boutonTexte}>S'inscrire →</Text>
      </TouchableOpacity>
    </ScrollView>
  );
}

export default AccueilScreen;