import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';

import { EnseignantAccueilComponent } from './pages/enseignant-accueil/enseignant-accueil.component';
import { MesCoursComponent } from './pages/mes-cours/mes-cours.component';
import { NouveauCoursComponent } from './pages/nouveau-cours/nouveau-cours.component';
import { ConversationsComponent } from './pages/conversations/conversations.component';
import { ProfilEnseignantComponent } from './pages/profil-enseignant/profil-enseignant.component';


const routes: Routes = [
  { path: 'accueil', component: EnseignantAccueilComponent },
  { path: 'mes-cours', component: MesCoursComponent },
  { path: 'nouveau-cours', component: NouveauCoursComponent },
  { path: 'conversations', component: ConversationsComponent },
  { path: 'profil', component: ProfilEnseignantComponent },
  { path: '', redirectTo: 'accueil', pathMatch: 'full' }
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule]
})
export class EnseignantRoutingModule { }