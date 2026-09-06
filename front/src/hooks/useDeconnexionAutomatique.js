import { useEffect, useRef } from 'react';
import { deconnecter } from '../services/authApi';

const DUREE_INACTIVITE_MS = 2 * 60 * 60 * 1000; // 2 heures

export function useDeconnexionAutomatique(onDeconnexion) {
  const minuteurRef = useRef(null);

  useEffect(() => {
    const reinitialiserMinuteur = () => {
      if (minuteurRef.current) {
        clearTimeout(minuteurRef.current);
      }

      minuteurRef.current = setTimeout(async () => {
        await deconnecter();
        onDeconnexion();
      }, DUREE_INACTIVITE_MS);
    };

    const evenements = ['mousemove', 'keydown', 'click', 'scroll'];
    evenements.forEach((evenement) => {
      window.addEventListener(evenement, reinitialiserMinuteur);
    });

    reinitialiserMinuteur();

    return () => {
      if (minuteurRef.current) {
        clearTimeout(minuteurRef.current);
      }
      evenements.forEach((evenement) => {
        window.removeEventListener(evenement, reinitialiserMinuteur);
      });
    };
  }, [onDeconnexion]);
}