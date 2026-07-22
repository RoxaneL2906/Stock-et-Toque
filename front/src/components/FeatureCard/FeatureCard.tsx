import { Image, Text, View } from 'react-native';
import { styles } from './FeatureCard.styles';

type Props = {
  icone: any;
  titre: string;
  description: string;
  couleur: string;
};

function FeatureCard({ icone, titre, description, couleur }: Props) {
  return (
    <View style={styles.card}>
      <Image source={icone} style={styles.icone} />
      <Text style={[styles.titre, { color: couleur }]}>{titre}</Text>
      <Text style={styles.description}>{description}</Text>
    </View>
  );
}

export default FeatureCard;