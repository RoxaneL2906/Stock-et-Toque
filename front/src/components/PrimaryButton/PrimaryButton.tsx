import { Text, TouchableOpacity } from 'react-native';
import { styles } from './PrimaryButton.styles';

type Props = {
  texte: string;
  onPress?: () => void;
};

function PrimaryButton({ texte, onPress }: Props) {
  return (
    <TouchableOpacity style={styles.bouton} onPress={onPress}>
      <Text style={styles.texte}>{texte}</Text>
    </TouchableOpacity>
  );
}

export default PrimaryButton;