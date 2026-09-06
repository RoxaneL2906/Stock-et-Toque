import './ToggleSwitch.css';

function ToggleSwitch({ Icone, couleur, texte, active, onChange }) {
  return (
    <div className="toggle-ligne">
      <div className="toggle-ligne-gauche">
        <Icone size={18} color={couleur} />
        <span className="toggle-ligne-texte">{texte}</span>
      </div>
      <button
        className={`toggle-switch ${active ? 'toggle-switch-actif' : ''}`}
        onClick={onChange}
        aria-label={texte}
      >
        <span className="toggle-switch-bouton" />
      </button>
    </div>
  );
}

export default ToggleSwitch;