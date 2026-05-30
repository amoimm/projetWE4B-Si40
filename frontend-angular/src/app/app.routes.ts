import { Routes } from '@angular/router';

import { EtudiantLayoutComponent } from './etudiant/pages/etudiant-layout/etudiant-layout';
import { EtudiantProfilComponent } from './etudiant/pages/etudiant-profil/etudiant-profil';
import { EtudiantAccueilComponent } from './etudiant/pages/etudiant-accueil/etudiant-accueil';

import { EnseignantModule } from './enseignant/enseignant.module';

export const routes: Routes = [
  // 1. Groupe des pages ÉTUDIANT 
  {
    path: 'etudiant',
    component: EtudiantLayoutComponent, // Le parent avec la nav
    children: [
      { path: 'accueil', component: EtudiantAccueilComponent },
      {  path: 'profil', component: EtudiantProfilComponent },
      // Tu ajouteras tes futures pages ici, elles auront toutes la nav automatiquement !
      // { path: 'accueil', component: EtudiantAccueilComponent },
    ]
  },

  // 2. ENSEIGNANT
  {
    path: 'enseignant',
    loadChildren: () => import('./enseignant/enseignant.module').then(m => m.EnseignantModule)
  },


  // Redirection par défaut
  { path: '', redirectTo: 'etudiant/accueil', pathMatch: 'full' }
];
