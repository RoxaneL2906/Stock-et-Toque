import { useRef, useState } from 'react';
import { ScrollView, Text, View } from 'react-native';
import Header from '../../components/Header/Header';
import FeatureCard from '../../components/FeatureCard/FeatureCard';
import PrimaryButton from '../../components/PrimaryButton/PrimaryButton';
import { styles } from './AccueilScreen.styles';

type Props = {
  onNaviguer: (ecran: 'accueil' | 'inscription' | 'connexion') => void;
};

function AccueilScreen({ onNaviguer }: Props) {
  const scrollViewRef = useRef<ScrollView>(null);
  const [positionFonctionnalites, setPositionFonctionnalites] = useState(0);

  const allerAuxFonctionnalites = () => {
    scrollViewRef.current?.scrollTo({ y: positionFonctionnalites, animated: true });
  };

  return (
    <View style={styles.ecranComplet}>
      <Header />

      <ScrollView
        ref={scrollViewRef}
        style={styles.container}
        contentContainerStyle={styles.contenu}
      >
        <Text style={styles.description}>
          Stock & Toque permet à l'utilisateur de gérer son stock de produits,
          planifier ses repas, organiser ses courses et accéder à des recettes
          filtrées selon ses préférences, son régime alimentaire et ses
          allergies.
        </Text>

        <View style={styles.boutonsColonne}>
          <PrimaryButton texte="Se connecter →" onPress={() => onNaviguer('connexion')} />
          <PrimaryButton texte="En savoir plus ↓" onPress={allerAuxFonctionnalites} />
        </View>

        <View
          style={styles.cardsGrid}
          onLayout={(event) => setPositionFonctionnalites(event.nativeEvent.layout.y)}
        >
          <FeatureCard
            icone={require('../../assets/images/icone-recettes.png')}
            titre="Recettes"
            description={'Privées & publiques.\nFiltrées par préférences'}
            couleur="#22C55E"
          />
          <FeatureCard
            icone={require('../../assets/images/icone-planning.png')}
            titre="Planning"
            description={'7 jours, midi & soir.\nSuggestions'}
            couleur="#F97316"
          />
          <FeatureCard
            icone={require('../../assets/images/icone-stock.png')}
            titre="Stock"
            description={'Frigo & Placard.\nAlertes DLC/DDM'}
            couleur="#60A5FA"
          />
          <FeatureCard
            icone={require('../../assets/images/icone-courses.png')}
            titre="Courses"
            description={'Manuelle ou auto.\nDepuis les recettes'}
            couleur="#A78BFA"
          />
        </View>

        <PrimaryButton texte="S'inscrire →" onPress={() => onNaviguer('inscription')} />
      </ScrollView>
    </View>
  );
}

export default AccueilScreen;