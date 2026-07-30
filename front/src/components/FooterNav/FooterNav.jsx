import { Home, Package, ShoppingCart, Utensils, Calendar, CircleUserRound } from 'lucide-react';
import './FooterNav.css';

const ELEMENTS_NAV = [
  { page: 'accueilConnecte', Icone: Home, couleur: '#EF4444' },
  { page: 'stock', Icone: Package, couleur: '#60A5FA' },
  { page: 'courses', Icone: ShoppingCart, couleur: '#A78BFA' },
  { page: 'recettes', Icone: Utensils, couleur: '#22C55E' },
  { page: 'planning', Icone: Calendar, couleur: '#F97316' },
  { page: 'profil', Icone: CircleUserRound, couleur: '#EF4444' },
];

function FooterNav({ pageActive, onNaviguer }) {
  return (
    <nav className="footer-nav">
      {ELEMENTS_NAV.map(({ page, Icone, couleur }) => (
        <button
          key={page}
          className="footer-nav-bouton"
          onClick={() => onNaviguer(page)}
          aria-label={page}
        >
          <Icone size={24} color={pageActive === page ? couleur : '#FFFFFF'} />
        </button>
      ))}
    </nav>
  );
}

export default FooterNav;