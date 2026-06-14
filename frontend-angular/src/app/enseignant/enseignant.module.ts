import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';


import { EnseignantAccueilComponent } from './pages/enseignant-accueil/enseignant-accueil.component';
import { MesCoursComponent } from './pages/mes-cours/mes-cours.component';
import { NouveauCoursComponent } from './pages/nouveau-cours/nouveau-cours.component';
import { ModifCoursComponent } from './pages/modif-cours/modif-cours.component';
import { ConversationsComponent } from './pages/conversations/conversations.component';


@NgModule({
  declarations: [],
  imports: [
    CommonModule,
    EnseignantAccueilComponent,
    MesCoursComponent,
    NouveauCoursComponent,
    ModifCoursComponent,
    ConversationsComponent,
  ]
})
export class EnseignantModule { }
