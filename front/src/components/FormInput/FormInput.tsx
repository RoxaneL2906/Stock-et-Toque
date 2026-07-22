import { useState } from 'react';
import { Text, TextInput, View } from 'react-native';
import { styles } from './FormInput.styles';

type Props = {
  label: string;
  value: string;
  onChangeText: (texte: string) => void;
  secureTextEntry?: boolean;
  placeholder?: string;
};

function FormInput({ label, value, onChangeText, secureTextEntry, placeholder }: Props) {
  const [enFocus, setEnFocus] = useState(false);

  return (
    <View style={styles.container}>
      <Text style={styles.label}>{label}</Text>
      <TextInput
        style={[styles.input, enFocus && styles.inputFocus]}
        value={value}
        onChangeText={onChangeText}
        secureTextEntry={secureTextEntry}
        placeholder={placeholder}
        placeholderTextColor="#6B7280"
        onFocus={() => setEnFocus(true)}
        onBlur={() => setEnFocus(false)}
      />
    </View>
  );
}

export default FormInput;