import './PrimaryButton.css';

function PrimaryButton({ texte, onClick }) {
  return (
    <button className="primary-button" onClick={onClick}>
      {texte}
    </button>
  );
}

export default PrimaryButton;