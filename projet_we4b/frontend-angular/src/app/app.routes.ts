import { Routes } from '@angular/router';
import { EtudiantLayoutComponent } from './etudiant/pages/etudiant-layout/etudiant-layout';
import { EtudiantProfilComponent } from './etudiant/pages/etudiant-profil/etudiant-profil';
// Importe ton composant de connexion ou enseignant ici plus tard

export const routes: Routes = [
  // 1. Groupe des pages ÉTUDIANT (avec la barre de navigation)
  {
    path: 'etudiant',
    component: EtudiantLayoutComponent, // Le parent avec la nav
    children: [
      { path: 'profil', component: EtudiantProfilComponent },
      // Tu ajouteras tes futures pages ici, elles auront toutes la nav automatiquement !
      // { path: 'accueil', component: EtudiantAccueilComponent },
    ]
  },

  // 2. Les pages HORS espace étudiant (Exemple : Connexion, Enseignant...)
  // Elles n'auront PAS la barre de navigation étudiant !
  // { path: 'connexion', component: ConnexionComponent },
  // { path: 'enseignant/accueil', component: EnseignantAccueilComponent },

  // Redirection par défaut
  { path: '', redirectTo: 'etudiant/profil', pathMatch: 'full' }
];
