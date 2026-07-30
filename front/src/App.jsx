import { useState, useEffect } from 'react';
import { useDeconnexionAutomatique } from './hooks/useDeconnexionAutomatique';
import AccueilScreen from './screens/Accueil/AccueilScreen';
import InscriptionScreen from './screens/Inscription/InscriptionScreen';
import ConnexionScreen from './screens/Connexion/ConnexionScreen';
import MotDePasseOublieScreen from './screens/MotDePasseOublie/MotDePasseOublieScreen';
import EmailEnvoyeScreen from './screens/EmailEnvoye/EmailEnvoyeScreen';
import ReinitialiserMotDePasseScreen from './screens/ReinitialiserMotDePasse/ReinitialiserMotDePasseScreen';
import ProfilScreen from './screens/Profil/ProfilScreen';
import ModifierInfosScreen from './screens/ModifierInfos/ModifierInfosScreen';
import ChangerMotDePasseScreen from './screens/ChangerMotDePasse/ChangerMotDePasseScreen';
import StockScreen from './screens/Stock/StockScreen';
import AjouterProduitScreen from './screens/AjouterProduit/AjouterProduitScreen';
import ListeCoursesScreen from './screens/ListeCourses/ListeCoursesScreen';
import AjouterArticleListeScreen from './screens/AjouterArticleListe/AjouterArticleListeScreen';
import AjouterRecetteScreen from './screens/AjouterRecette/AjouterRecetteScreen';
import MesRecettesScreen from './screens/MesRecettes/MesRecettesScreen';
import RecetteDetailScreen from './screens/RecetteDetail/RecetteDetailScreen';
import ModifierRecetteScreen from './screens/ModifierRecette/ModifierRecetteScreen';
import RecettesPubliquesScreen from './screens/RecettesPubliques/RecettesPubliquesScreen';
import RecettePubliqueDetailScreen from './screens/RecettePubliqueDetail/RecettePubliqueDetailScreen';
import MesFavorisScreen from './screens/MesFavoris/MesFavorisScreen';
import AccueilConnecteScreen from './screens/AccueilConnecte/AccueilConnecteScreen';

function App() {
  const [ecranActuel, setEcranActuel] = useState('accueil');
  const [recetteSelectionneeId, setRecetteSelectionneeId] = useState(null);

  useEffect(() => {
    const parametres = new URLSearchParams(window.location.search);
    if (parametres.get('token')) {
      setEcranActuel('reinitialiserMotDePasse');
    }
  }, []);

  useDeconnexionAutomatique(() => setEcranActuel('connexion'));

  const naviguerVersRecette = (id) => {
    setRecetteSelectionneeId(id);
    setEcranActuel('recetteDetail');
  };

  const naviguerVersModificationRecette = () => {
    setEcranActuel('modifierRecette');
  };

  const naviguerVersRecettePublique = (id) => {
    setRecetteSelectionneeId(id);
    setEcranActuel('recettePublique');
  };

  return (
    <>
      {ecranActuel === 'accueil' && <AccueilScreen onNaviguer={setEcranActuel} />}
      {ecranActuel === 'inscription' && <InscriptionScreen onNaviguer={setEcranActuel} />}
      {ecranActuel === 'connexion' && <ConnexionScreen onNaviguer={setEcranActuel} />}
      {ecranActuel === 'motDePasseOublie' && <MotDePasseOublieScreen onNaviguer={setEcranActuel} />}
      {ecranActuel === 'emailEnvoye' && <EmailEnvoyeScreen onNaviguer={setEcranActuel} />}
      {ecranActuel === 'reinitialiserMotDePasse' && <ReinitialiserMotDePasseScreen onNaviguer={setEcranActuel} />}
      {ecranActuel === 'accueilConnecte' && <AccueilConnecteScreen onNaviguer={setEcranActuel} />}
      {ecranActuel === 'profil' && <ProfilScreen onNaviguer={setEcranActuel} />}
      {ecranActuel === 'modifierInfos' && <ModifierInfosScreen onNaviguer={setEcranActuel} />}
      {ecranActuel === 'changerMotDePasse' && <ChangerMotDePasseScreen onNaviguer={setEcranActuel} />}
      {ecranActuel === 'stock' && <StockScreen onNaviguer={setEcranActuel} />}
      {ecranActuel === 'ajouterProduit' && <AjouterProduitScreen onNaviguer={setEcranActuel} />}
      {ecranActuel === 'courses' && <ListeCoursesScreen onNaviguer={setEcranActuel} />}
      {ecranActuel === 'ajouterArticleListe' && <AjouterArticleListeScreen onNaviguer={setEcranActuel} />}
      {ecranActuel === 'ajouterRecette' && <AjouterRecetteScreen onNaviguer={setEcranActuel} />}
      {ecranActuel === 'mesRecettes' && (
        <MesRecettesScreen onNaviguer={setEcranActuel} onNaviguerVersRecette={naviguerVersRecette} />
      )}
      {ecranActuel === 'recetteDetail' && (
        <RecetteDetailScreen
          recetteId={recetteSelectionneeId}
          onNaviguer={setEcranActuel}
          onNaviguerVersModification={naviguerVersModificationRecette}
        />
      )}
      {ecranActuel === 'modifierRecette' && (
        <ModifierRecetteScreen recetteId={recetteSelectionneeId} onNaviguer={setEcranActuel} />
      )}
      {ecranActuel === 'recettes' && (
        <RecettesPubliquesScreen
          onNaviguer={setEcranActuel}
          onNaviguerVersRecettePublique={naviguerVersRecettePublique}
        />
      )}
      {ecranActuel === 'recettePublique' && (
        <RecettePubliqueDetailScreen recetteId={recetteSelectionneeId} onNaviguer={setEcranActuel} />
      )}
      {ecranActuel === 'mesFavoris' && (
        <MesFavorisScreen onNaviguer={setEcranActuel} onNaviguerVersRecettePublique={naviguerVersRecettePublique} />
      )}
    </>
  );
}

export default App;