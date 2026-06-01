import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { EnseignantRoutingModule } from './enseignant-routing.module';


import { EnseignantAccueilComponent } from './pages/enseignant-accueil/enseignant-accueil.component';
import { MesCoursComponent } from './pages/mes-cours/mes-cours.component';
import { NouveauCoursComponent } from './pages/nouveau-cours/nouveau-cours.component';
import { ConversationsComponent } from './pages/conversations/conversations.component';
import { ProfilEnseignantComponent } from './pages/profil-enseignant/profil-enseignant.component';


@NgModule({
  declarations: [],
  imports: [
    CommonModule,
    EnseignantRoutingModule,
    EnseignantAccueilComponent,
    MesCoursComponent,
    NouveauCoursComponent,
    ConversationsComponent,
    ProfilEnseignantComponent
  ]
})
export class EnseignantModule { }