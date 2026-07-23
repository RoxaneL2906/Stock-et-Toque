import logo from '../../assets/images/logo.png';
import './Header.css';

function Header() {
  return (
    <header className="header">
      <img src={logo} alt="Logo Stock & Toque" className="header-logo" />
      <h1 className="header-titre">
        Stock <span className="header-titre-accent">& Toque</span>
      </h1>
      <p className="header-sous-titre">
        Parce que bien manger ne devrait pas être une corvée
      </p>
    </header>
  );
}

export default Header;