import './PrimaryButton.css';

function PrimaryButton({ texte, onClick, type = 'button' }) {
  return (
    <button className="primary-button" onClick={onClick} type={type}>
      {texte}
    </button>
  );
}

export default PrimaryButton;