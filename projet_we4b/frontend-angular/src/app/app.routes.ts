import { Routes } from '@angular/router';
import { EtudiantProfilComponent } from './etudiant/pages/etudiant-profil/etudiant-profil';

export const routes: Routes = [
    // Redirection automatique vers la page profil si l'URL est vide
    { path: '', redirectTo: 'etudiant/profil', pathMatch: 'full' },

    // Route d'accès à ton composant profil
    { path: 'etudiant/profil', component: EtudiantProfilComponent }
];
