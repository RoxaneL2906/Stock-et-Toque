import { ArrowLeft } from 'lucide-react';
import './BoutonRetour.css';

function BoutonRetour({ onClick, texte = 'Retour' }) {
  return (
    <button className="bouton-retour" onClick={onClick}>
      <ArrowLeft size={18} color="var(--couleur-vert)" />
      <span>{texte}</span>
    </button>
  );
}

export default BoutonRetour;