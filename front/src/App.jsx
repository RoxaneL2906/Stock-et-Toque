// Bibliothèques externes
import { useState, useEffect } from 'react';
import { Helmet } from 'react-helmet-async';

// Hooks personnalisés
import { useDeconnexionAutomatique } from './hooks/useDeconnexionAutomatique';

// Écrans visiteur
import AccueilScreen from './screens/Accueil/AccueilScreen';
import InscriptionScreen from './screens/Inscription/InscriptionScreen';
import ConnexionScreen from './screens/Connexion/ConnexionScreen';
import MotDePasseOublieScreen from './screens/MotDePasseOublie/MotDePasseOublieScreen';
import EmailEnvoyeScreen from './screens/EmailEnvoye/EmailEnvoyeScreen';
import ReinitialiserMotDePasseScreen from './screens/ReinitialiserMotDePasse/ReinitialiserMotDePasseScreen';

// Écrans profil
import AccueilConnecteScreen from './screens/AccueilConnecte/AccueilConnecteScreen';
import ProfilScreen from './screens/Profil/ProfilScreen';
import ModifierInfosScreen from './screens/ModifierInfos/ModifierInfosScreen';
import ChangerMotDePasseScreen from './screens/ChangerMotDePasse/ChangerMotDePasseScreen';

// Écrans stock
import StockScreen from './screens/Stock/StockScreen';
import AjouterProduitScreen from './screens/AjouterProduit/AjouterProduitScreen';

// Écrans courses
import ListeCoursesScreen from './screens/ListeCourses/ListeCoursesScreen';
import AjouterArticleListeScreen from './screens/AjouterArticleListe/AjouterArticleListeScreen';

// Écrans recettes privées
import AjouterRecetteScreen from './screens/AjouterRecette/AjouterRecetteScreen';
import MesRecettesScreen from './screens/MesRecettes/MesRecettesScreen';
import RecetteDetailScreen from './screens/RecetteDetail/RecetteDetailScreen';
import ModifierRecetteScreen from './screens/ModifierRecette/ModifierRecetteScreen';

// Écrans recettes publiques
import RecettesPubliquesScreen from './screens/RecettesPubliques/RecettesPubliquesScreen';
import RecettePubliqueDetailScreen from './screens/RecettePubliqueDetail/RecettePubliqueDetailScreen';
import MesFavorisScreen from './screens/MesFavoris/MesFavorisScreen';

// Écran planning
import PlanningScreen from './screens/Planning/PlanningScreen';

function App() {
  const [ecranActuel, setEcranActuel] = useState('accueil');
  const [recetteSelectionneeId, setRecetteSelectionneeId] = useState(null);
  const [creneauCible, setCreneauCible] = useState(null);

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

  const naviguerVersChoixRecette = (cible) => {
    setCreneauCible(cible);
    setEcranActuel('recettes');
  };

  return (
    <>
      <Helmet>
        <title>Stock & Toque</title>
        <meta name="description" content="Application de gestion de stock alimentaire, planning de repas, liste de courses et recettes de cuisine." />
      </Helmet>
      {/* Écrans visiteur */}
      {ecranActuel === 'accueil' && <AccueilScreen onNaviguer={setEcranActuel} />}
      {ecranActuel === 'inscription' && <InscriptionScreen onNaviguer={setEcranActuel} />}
      {ecranActuel === 'connexion' && <ConnexionScreen onNaviguer={setEcranActuel} />}
      {ecranActuel === 'motDePasseOublie' && <MotDePasseOublieScreen onNaviguer={setEcranActuel} />}
      {ecranActuel === 'emailEnvoye' && <EmailEnvoyeScreen onNaviguer={setEcranActuel} />}
      {ecranActuel === 'reinitialiserMotDePasse' && <ReinitialiserMotDePasseScreen onNaviguer={setEcranActuel} />}

      {/* Écrans profil */}
      {ecranActuel === 'accueilConnecte' && (
        <AccueilConnecteScreen onNaviguer={setEcranActuel} onNaviguerVersRecettePublique={naviguerVersRecettePublique} />
      )}
      {ecranActuel === 'profil' && <ProfilScreen onNaviguer={setEcranActuel} />}
      {ecranActuel === 'modifierInfos' && <ModifierInfosScreen onNaviguer={setEcranActuel} />}
      {ecranActuel === 'changerMotDePasse' && <ChangerMotDePasseScreen onNaviguer={setEcranActuel} />}

      {/* Écrans stock */}
      {ecranActuel === 'stock' && <StockScreen onNaviguer={setEcranActuel} />}
      {ecranActuel === 'ajouterProduit' && <AjouterProduitScreen onNaviguer={setEcranActuel} />}

      {/* Écrans courses */}
      {ecranActuel === 'courses' && <ListeCoursesScreen onNaviguer={setEcranActuel} />}
      {ecranActuel === 'ajouterArticleListe' && <AjouterArticleListeScreen onNaviguer={setEcranActuel} />}

      {/* Écrans recettes privées */}
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

      {/* Écrans recettes publiques */}
      {ecranActuel === 'recettes' && (
        <RecettesPubliquesScreen
          onNaviguer={setEcranActuel}
          onNaviguerVersRecettePublique={naviguerVersRecettePublique}
        />
      )}
      {ecranActuel === 'recettePublique' && (
        <RecettePubliqueDetailScreen
          recetteId={recetteSelectionneeId}
          onNaviguer={setEcranActuel}
          creneauCible={creneauCible}
          onCreneauAjoute={() => {
            setCreneauCible(null);
            setEcranActuel('planning');
          }}
        />
      )}
      {ecranActuel === 'mesFavoris' && (
        <MesFavorisScreen onNaviguer={setEcranActuel} onNaviguerVersRecettePublique={naviguerVersRecettePublique} />
      )}

      {/* Écran planning */}
      {ecranActuel === 'planning' && (
        <PlanningScreen
          onNaviguer={setEcranActuel}
          onNaviguerVersChoixRecette={naviguerVersChoixRecette}
          onNaviguerVersRecettePublique={naviguerVersRecettePublique}
        />
      )}
    </>
  );
}

export default App;