import { Image, Text, View } from 'react-native';
import { styles } from './Header.styles';

function Header() {
  return (
    <View style={styles.container}>
      <Image
        source={require('../../assets/images/logo.png')}
        style={styles.logo}
      />
      <Text style={styles.titre}>
        Stock <Text style={styles.titreAccent}>& Toque</Text>
      </Text>
      <Text style={styles.sousTitre}>
        Parce que bien manger ne devrait pas être une corvée
      </Text>
    </View>
  );
}

export default Header;