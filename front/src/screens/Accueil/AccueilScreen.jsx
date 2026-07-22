import { useRef } from 'react';
import Header from '../../components/Header/Header';
import FeatureCard from '../../components/FeatureCard/FeatureCard';
import PrimaryButton from '../../components/PrimaryButton/PrimaryButton';
import iconeRecettes from '../../assets/images/icone-recettes.png';
import iconePlanning from '../../assets/images/icone-planning.png';
import iconeStock from '../../assets/images/icone-stock.png';
import iconeCourses from '../../assets/images/icone-courses.png';
import './AccueilScreen.css';

function AccueilScreen({ onNaviguer }) {
  const fonctionnalitesRef = useRef(null);

  const allerAuxFonctionnalites = () => {
    fonctionnalitesRef.current?.scrollIntoView({ behavior: 'smooth' });
  };

  return (
    <div className="ecran-complet">
      <Header />
      <div className="ecran-contenu">
        <p className="accueil-description">
          Stock & Toque permet à l'utilisateur de gérer son stock de produits,
          planifier ses repas, organiser ses courses et accéder à des recettes
          filtrées selon ses préférences, son régime alimentaire et ses allergies.
        </p>

        <div className="accueil-boutons-colonne">
          <PrimaryButton texte="Se connecter →" onClick={() => onNaviguer('connexion')} />
          <PrimaryButton texte="En savoir plus ↓" onClick={allerAuxFonctionnalites} />
        </div>

        <div className="accueil-cards-grid" ref={fonctionnalitesRef}>
          <FeatureCard icone={iconeRecettes} titre="Recettes" description={'Privées & publiques.\nFiltrées par préférences'} couleur="#22C55E" />
          <FeatureCard icone={iconePlanning} titre="Planning" description={'7 jours, midi & soir.\nSuggestions'} couleur="#F97316" />
          <FeatureCard icone={iconeStock} titre="Stock" description={'Frigo & Placard.\nAlertes DLC/DDM'} couleur="#60A5FA" />
          <FeatureCard icone={iconeCourses} titre="Courses" description={'Manuelle ou auto.\nDepuis les recettes'} couleur="#A78BFA" />
        </div>

        <PrimaryButton texte="S'inscrire →" onClick={() => onNaviguer('inscription')} />
      </div>
    </div>
  );
}

export default AccueilScreen;