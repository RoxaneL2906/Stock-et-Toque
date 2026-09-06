import { ChevronRight } from 'lucide-react';
import './NavigationChevron.css';

function NavigationChevron({ Icone, couleur, texte, onClick }) {
  return (
    <button className="navigation-chevron" onClick={onClick}>
      <div className="navigation-chevron-gauche">
        <Icone size={18} color={couleur} />
        <span className="navigation-chevron-texte">{texte}</span>
      </div>
      <ChevronRight size={18} color="#9CA3AF" />
    </button>
  );
}

export default NavigationChevron;