import './FeatureCard.css';

function FeatureCard({ icone, titre, description, couleur }) {
  return (
    <div className="feature-card">
      <img src={icone} alt={titre} className="feature-card-icone" />
      <h3 className="feature-card-titre" style={{ color: couleur }}>{titre}</h3>
      <p className="feature-card-description">{description}</p>
    </div>
  );
}

export default FeatureCard;