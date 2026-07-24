import logo from '../../assets/images/logo.png';
import { Bell } from 'lucide-react';
import './HeaderAppli.css';

function HeaderAppli() {
  return (
    <header className="header-appli">
      <div className="header-appli-gauche">
        <img src={logo} alt="Logo Stock & Toque" className="header-appli-logo" />
        <h1 className="header-appli-titre">
          Stock <span className="header-appli-titre-accent">& Toque</span>
        </h1>
      </div>
      <Bell className="header-appli-icone-notif" size={22} />
    </header>
  );
}

export default HeaderAppli;