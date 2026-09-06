import './FormInput.css';

function FormInput({ label, value, onChange, type = 'text', modifie = false }) {
  return (
    <div className="form-input-container">
      {label && (
        <label className="form-input-label">
          {label}
          {modifie && <span className="form-input-point-modif" />}
        </label>
      )}
      <input
        className="form-input"
        type={type}
        value={value}
        onChange={(e) => onChange(e.target.value)}
      />
    </div>
  );
}

export default FormInput;