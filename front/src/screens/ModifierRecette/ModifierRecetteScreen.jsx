import { useState, useEffect } from 'react';
import { Camera, X, Minus, Plus, ChefHat, Lock, Unlock, Clock, Heater } from 'lucide-react';
import HeaderAppli from '../../components/HeaderAppli/HeaderAppli';
import FooterNav from '../../components/FooterNav/FooterNav';
import PrimaryButton from '../../components/PrimaryButton/PrimaryButton';
import { consulterMaRecette, modifierRecette, listerEquipements, uploaderPhotoRecette } from '../../services/recetteApi';
import '../AjouterRecette/AjouterRecetteScreen.css';

const NIVEAUX_DIFFICULTE = [
  { valeur: 'facile', nbToquesColorees: 1 },
  { valeur: 'moyen', nbToquesColorees: 2 },
  { valeur: 'difficile', nbToquesColorees: 3 },
];

function ModifierRecetteScreen({ recetteId, onNaviguer }) {
  const [chargementInitial, setChargementInitial] = useState(true);
  const [photo, setPhoto] = useState(null);
  const [apercuPhoto, setApercuPhoto] = useState(null);
  const [titre, setTitre] = useState('');
  const [description, setDescription] = useState('');
  const [ingredients, setIngredients] = useState([]);
  const [tempsPreparation, setTempsPreparation] = useState('');
  const [tempsCuisson, setTempsCuisson] = useState('');
  const [equipementsDisponibles, setEquipementsDisponibles] = useState([]);
  const [equipementsChoisis, setEquipementsChoisis] = useState([]);
  const [afficherTousEquipements, setAfficherTousEquipements] = useState(false);
  const [nbPersonnes, setNbPersonnes] = useState(2);
  const [budgetEstime, setBudgetEstime] = useState('');
  const [difficulte, setDifficulte] = useState(null);
  const [estPrivee, setEstPrivee] = useState(true);
  const [estBrouillon, setEstBrouillon] = useState(false);
  const [etapes, setEtapes] = useState(['']);

  const [erreur, setErreur] = useState('');
  const [succes, setSucces] = useState('');
  const [chargement, setChargement] = useState(false);

  useEffect(() => {
    listerEquipements().then(setEquipementsDisponibles).catch(() => setEquipementsDisponibles([]));

    consulterMaRecette(recetteId)
      .then((recette) => {
        setTitre(recette.titre);
        setDescription(recette.description || '');
        setApercuPhoto(recette.photo);
        setTempsPreparation(recette.tempsPreparation !== null ? String(recette.tempsPreparation) : '');
        setTempsCuisson(recette.tempsCuisson !== null ? String(recette.tempsCuisson) : '');
        setNbPersonnes(recette.nbPersonnes);
        setBudgetEstime(recette.budgetEstime || '');
        setDifficulte(recette.difficulte);
        setEstPrivee(recette.visibilite === 'privee');
        setEstBrouillon(recette.brouillon);
        setEquipementsChoisis(recette.equipements.map((e) => e.id));
        setIngredients(
          recette.ingredients.length > 0
            ? recette.ingredients.map((i) => ({
                nom: i.nom,
                quantite: i.quantite !== null ? String(parseFloat(i.quantite)) : '',
                unite: i.unite || '',
                alternative: '',
              }))
            : [{ nom: '', quantite: '', unite: '', alternative: '' }]
        );
        setEtapes(
          recette.etapes.length > 0
            ? recette.etapes.map((e) => e.description)
            : ['']
        );
        setChargementInitial(false);
      })
      .catch((err) => {
        setErreur(err.message);
        setChargementInitial(false);
      });
  }, [recetteId]);

  const gererChoixPhoto = (e) => {
    const fichier = e.target.files[0];
    if (fichier) {
      setPhoto(fichier);
      setApercuPhoto(URL.createObjectURL(fichier));
    }
  };

  const modifierIngredient = (index, champ, valeur) => {
    const copie = [...ingredients];
    copie[index][champ] = valeur;
    setIngredients(copie);
  };

  const ajouterIngredient = () => {
    setIngredients([...ingredients, { nom: '', quantite: '', unite: '', alternative: '' }]);
  };

  const supprimerIngredient = (index) => {
    setIngredients(ingredients.filter((_, i) => i !== index));
  };

  const modifierEtape = (index, valeur) => {
    const copie = [...etapes];
    copie[index] = valeur;
    setEtapes(copie);
  };

  const ajouterEtape = () => {
    setEtapes([...etapes, '']);
  };

  const supprimerEtape = (index) => {
    setEtapes(etapes.filter((_, i) => i !== index));
  };

  const basculerEquipement = (id) => {
    setEquipementsChoisis((prev) =>
      prev.includes(id) ? prev.filter((e) => e !== id) : [...prev, id]
    );
  };

  const soumettre = async () => {
    setErreur('');
    setSucces('');
    setChargement(true);

    try {
      await modifierRecette(recetteId, {
        titre,
        description: description || null,
        tempsPreparation: tempsPreparation ? parseInt(tempsPreparation, 10) : null,
        tempsCuisson: tempsCuisson ? parseInt(tempsCuisson, 10) : null,
        nbPersonnes,
        difficulte,
        budgetEstime: budgetEstime || null,
        visibilite: estPrivee ? 'privee' : 'publique',
        brouillon: estBrouillon,
        ingredients: ingredients
          .filter((i) => i.nom.trim() !== '')
          .map((i) => ({
            nom: i.nom,
            quantite: i.quantite ? parseFloat(i.quantite) : null,
            unite: i.unite || null,
          })),
        etapes: etapes.filter((e) => e.trim() !== ''),
        equipementIds: equipementsChoisis,
      });

      if (photo) {
        await uploaderPhotoRecette(recetteId, photo);
      }

      setSucces('Recette modifiée avec succès.');
      setTimeout(() => onNaviguer('recetteDetail'), 1200);
    } catch (err) {
      setErreur(err.message);
    } finally {
      setChargement(false);
    }
  };

  if (chargementInitial) {
    return <p style={{ color: '#fff' }}>Chargement...</p>;
  }

  return (
    <div className="ecran-complet">
      <HeaderAppli />
      <div className="ecran-contenu">

        <h2 className="ajout-recette-titre">Modifier "{titre}"</h2>

        <label className="ajout-recette-photo-zone">
          <input type="file" accept="image/*" onChange={gererChoixPhoto} hidden />
          {apercuPhoto ? (
            <img src={apercuPhoto} alt="Aperçu" className="ajout-recette-photo-apercu" />
          ) : (
            <div className="ajout-recette-photo-contenu">
              <Camera size={32} color="#FFFFFF" />
              <span className="ajout-recette-photo-texte">Changer la photo</span>
              <span className="ajout-recette-photo-optionnel">Optionnel</span>
            </div>
          )}
        </label>

        <div className="ajout-recette-champ">
          <label className="ajout-recette-label-vert">Titre</label>
          <input
            type="text"
            value={titre}
            onChange={(e) => setTitre(e.target.value)}
            className="ajout-recette-input"
          />
        </div>

        <div className="ajout-recette-champ">
          <label className="ajout-recette-label-vert">Description (optionnelle)</label>
          <textarea
            className="ajout-recette-textarea"
            value={description}
            onChange={(e) => setDescription(e.target.value)}
          />
        </div>

        <div className="ajout-recette-section-entete">
          <h3 className="ajout-recette-section-titre-inline">Ingrédients</h3>
          <button type="button" className="ajout-recette-lien-ajouter" onClick={ajouterIngredient}>
            Ajouter <Plus size={14} color="var(--couleur-vert)" />
          </button>
        </div>

        {ingredients.map((ingredient, index) => (
          <div className="ajout-recette-ingredient-bloc" key={index}>
            <div className="ajout-recette-ingredient-ligne">
              <input
                type="text"
                placeholder="Ingrédient"
                value={ingredient.nom}
                onChange={(e) => modifierIngredient(index, 'nom', e.target.value)}
                className="ajout-recette-input-inline"
              />
              <input
                type="text"
                placeholder="Quantité"
                value={ingredient.quantite}
                onChange={(e) => modifierIngredient(index, 'quantite', e.target.value)}
                className="ajout-recette-input-inline"
              />
            </div>
            <div className="ajout-recette-alternative-ligne">
              <input
                type="text"
                placeholder="Alternative s'il y en a une"
                value={ingredient.alternative}
                onChange={(e) => modifierIngredient(index, 'alternative', e.target.value)}
                className="ajout-recette-input-alternative"
              />
              {ingredients.length > 1 && (
                <button type="button" onClick={() => supprimerIngredient(index)} className="ajout-recette-x">
                  <X size={16} color="var(--couleur-texte-clair)" />
                </button>
              )}
            </div>
          </div>
        ))}

        <button type="button" className="ajout-recette-bouton-nouvel-ingredient" onClick={ajouterIngredient}>
          + Nouvel ingrédient
        </button>

        <h3 className="ajout-recette-section-titre">Préparation & Matériel</h3>

        <div className="ajout-recette-temps-grid">
          <div className="ajout-recette-temps-case">
            <Clock size={18} color="var(--couleur-orange)" />
            <span className="ajout-recette-temps-label">Préparation</span>
            <input
              type="text"
              inputMode="numeric"
              value={tempsPreparation}
              onChange={(e) => setTempsPreparation(e.target.value.replace(/\D/g, ''))}
              className="ajout-recette-temps-input"
              placeholder="0"
            />
            <span className="ajout-recette-temps-unite">mn</span>
          </div>
          <div className="ajout-recette-temps-case">
            <Heater size={18} color="var(--couleur-orange)" />
            <span className="ajout-recette-temps-label">Cuisson</span>
            <input
              type="text"
              inputMode="numeric"
              value={tempsCuisson}
              onChange={(e) => setTempsCuisson(e.target.value.replace(/\D/g, ''))}
              className="ajout-recette-temps-input"
              placeholder="0"
            />
            <span className="ajout-recette-temps-unite">mn</span>
          </div>
        </div>

        <h3 className="ajout-recette-section-titre">Équipements requis</h3>
        <div className="ajout-recette-equipements-grid">
          {equipementsDisponibles
            .filter((e) => equipementsChoisis.includes(e.id))
            .map((equipement) => (
              <button
                type="button"
                key={equipement.id}
                className="ajout-recette-equip-choix-actif"
                onClick={() => basculerEquipement(equipement.id)}
              >
                {equipement.nom} <X size={14} color="#111827" />
              </button>
            ))}
          <button
            type="button"
            className="ajout-recette-equip-choix"
            onClick={() => setAfficherTousEquipements(!afficherTousEquipements)}
          >
            + Ajouter un équipement
          </button>
        </div>

        {afficherTousEquipements && (
          <div className="ajout-recette-equipements-liste-complete">
            {equipementsDisponibles
              .filter((e) => !equipementsChoisis.includes(e.id))
              .map((equipement) => (
                <button
                  type="button"
                  key={equipement.id}
                  className="ajout-recette-equip-choix"
                  onClick={() => basculerEquipement(equipement.id)}
                >
                  {equipement.nom}
                </button>
              ))}
          </div>
        )}

        <h3 className="ajout-recette-section-titre">Étapes</h3>
        <div className="ajout-recette-etapes-section">
          <div className="ajout-recette-section-entete">
            <span />
            <button type="button" className="ajout-recette-lien-ajouter" onClick={ajouterEtape}>
              Ajouter <Plus size={14} color="var(--couleur-vert)" />
            </button>
          </div>
          {etapes.map((etape, index) => (
            <div className="ajout-recette-etape-ligne" key={index}>
              <span className="ajout-recette-etape-numero">{index + 1}</span>
              <textarea
                className="ajout-recette-etape-textarea"
                value={etape}
                onChange={(e) => modifierEtape(index, e.target.value)}
                placeholder={`Étape ${index + 1}`}
              />
              {etapes.length > 1 && (
                <button type="button" onClick={() => supprimerEtape(index)} className="ajout-recette-x">
                  <X size={16} color="#EF4444" />
                </button>
              )}
            </div>
          ))}
        </div>

        <div className="ajout-recette-portions-budget">
          <div className="ajout-recette-portions">
            <label className="form-input-label">Portions (/pers.)</label>
            <div className="ajout-recette-pilule">
              <button type="button" onClick={() => setNbPersonnes(Math.max(1, nbPersonnes - 1))} aria-label="Diminuer">
                <Minus size={16} color="#FFFFFF" />
              </button>
              <span>{nbPersonnes}</span>
              <button type="button" onClick={() => setNbPersonnes(nbPersonnes + 1)} aria-label="Augmenter">
                <Plus size={16} color="#FFFFFF" />
              </button>
            </div>
          </div>
          <div className="ajout-recette-budget">
            <label className="form-input-label">Budget estimé</label>
            <div className="ajout-recette-pilule">
              <input
                type="text"
                inputMode="decimal"
                value={budgetEstime}
                onChange={(e) => setBudgetEstime(e.target.value)}
                className="ajout-recette-budget-input"
                placeholder="0"
              />
              <span>€</span>
            </div>
          </div>
        </div>

        <h3 className="ajout-recette-section-titre">Difficulté</h3>
        <div className="ajout-recette-difficulte-ligne">
          {NIVEAUX_DIFFICULTE.map((niveau) => (
            <button
              type="button"
              key={niveau.valeur}
              className={difficulte === niveau.valeur ? 'ajout-recette-difficulte-bouton-actif' : 'ajout-recette-difficulte-bouton'}
              onClick={() => setDifficulte(niveau.valeur)}
            >
              {[0, 1, 2].map((i) => (
                <ChefHat key={i} size={18} color={i < niveau.nbToquesColorees ? 'var(--couleur-orange)' : '#4B5563'} />
              ))}
            </button>
          ))}
        </div>

        <div className="ajout-recette-visibilite">
          <div className="ajout-recette-visibilite-gauche">
            {estPrivee ? <Lock size={18} color="var(--couleur-texte-clair)" /> : <Unlock size={18} color="var(--couleur-texte-clair)" />}
            <div>
              <p className="ajout-recette-visibilite-titre">Recette {estPrivee ? 'privée' : 'publique'}</p>
              <p className="ajout-recette-visibilite-sous-texte">
                {estPrivee ? 'Visible uniquement par vous' : 'Visible par toute la communauté'}
              </p>
            </div>
          </div>
          <button
            type="button"
            className={estPrivee ? 'ajout-recette-toggle' : 'ajout-recette-toggle ajout-recette-toggle-actif'}
            onClick={() => setEstPrivee(!estPrivee)}
          >
            <span className="ajout-recette-toggle-bouton" />
          </button>
        </div>

        <div className="ajout-recette-visibilite">
          <div className="ajout-recette-visibilite-gauche">
            <div>
              <p className="ajout-recette-visibilite-titre">{estBrouillon ? 'Brouillon' : 'Recette finalisée'}</p>
              <p className="ajout-recette-visibilite-sous-texte">
                {estBrouillon ? "N'apparaît pas dans les recherches" : 'Prête à être consultée'}
              </p>
            </div>
          </div>
          <button
            type="button"
            className={!estBrouillon ? 'ajout-recette-toggle' : 'ajout-recette-toggle ajout-recette-toggle-actif'}
            onClick={() => setEstBrouillon(!estBrouillon)}
          >
            <span className="ajout-recette-toggle-bouton" />
          </button>
        </div>

        {erreur && <p className="message-erreur">{erreur}</p>}
        {succes && <p className="message-succes">{succes}</p>}

        <PrimaryButton
          texte={chargement ? 'Enregistrement...' : 'Enregistrer les modifications'}
          onClick={soumettre}
        />

      </div>
      <FooterNav pageActive="recettes" onNaviguer={onNaviguer} />
    </div>
  );
}

export default ModifierRecetteScreen;