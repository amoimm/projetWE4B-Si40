import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';

// Importation des composants
import { AdminLayout } from './components/admin-layout/admin-layout';
import { AdminAccueil } from './pages/admin-accueil/admin-accueil';
import { AdminUtilisateurs } from './pages/admin-utilisateurs/admin-utilisateurs';
import { AdminCours } from './pages/admin-cours/admin-cours';
import { AdminConfig } from './pages/admin-config/admin-config';

@NgModule({
  declarations: [],
  imports: [
    CommonModule,
    AdminLayout,
    AdminAccueil,
    AdminUtilisateurs,
    AdminCours,
    AdminConfig
  ]
})
export class AdminModule { }

