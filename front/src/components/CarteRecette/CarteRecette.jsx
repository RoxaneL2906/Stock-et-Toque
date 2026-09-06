import { Lock, Unlock, Pencil, Clock } from 'lucide-react';
import photoDefaut from '../../assets/images/recetteDefaut.png';
import './CarteRecette.css';

function CarteRecette({ recette, onClick }) {
  const estBrouillon = recette.brouillon;
  const estPublique = recette.visibilite === 'publique';

  return (
    <button className="carte-recette" onClick={onClick}>
      <div className={estBrouillon ? 'carte-recette-image carte-recette-image-grisee' : 'carte-recette-image'}>
        <img src={recette.photo || photoDefaut} alt={recette.titre} />

        <div className="carte-recette-badge">
          {estBrouillon ? (
            <Pencil size={16} color="#FFFFFF" />
          ) : estPublique ? (
            <Unlock size={16} color="#FFFFFF" />
          ) : (
            <Lock size={16} color="#FFFFFF" />
          )}
        </div>

        <div className="carte-recette-overlay">
          <p className="carte-recette-titre">{recette.titre}</p>
          {recette.tempsPreparation !== null && (
            <p className="carte-recette-temps">
              <Clock size={12} color="#FFFFFF" /> {recette.tempsPreparation} mn
            </p>
          )}
        </div>
      </div>
    </button>
  );
}

export default CarteRecette;