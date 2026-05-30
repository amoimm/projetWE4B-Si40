import { Routes } from '@angular/router';

import { EtudiantLayoutComponent } from './etudiant/pages/etudiant-layout/etudiant-layout';
import { EtudiantProfilComponent } from './etudiant/pages/etudiant-profil/etudiant-profil';
import { EtudiantAccueilComponent } from './etudiant/pages/etudiant-accueil/etudiant-accueil';
import { EtudiantChatComponent } from './etudiant/pages/etudiant-chat/etudiant-chat';
import { EtudiantConversationComponent } from './etudiant/pages/etudiant-conversation/etudiant-conversation';


import { EnseignantLayout } from './enseignant/pages/enseignant-layout/enseignant-layout';
import { EnseignantAccueilComponent } from './enseignant/pages/enseignant-accueil/enseignant-accueil.component';
import { MesCoursComponent } from './enseignant/pages/mes-cours/mes-cours.component';
import { NouveauCoursComponent } from './enseignant/pages/nouveau-cours/nouveau-cours.component';
import { ConversationsComponent } from './enseignant/pages/conversations/conversations.component';
import { ProfilEnseignantComponent } from './enseignant/pages/profil-enseignant/profil-enseignant.component';




export const routes: Routes = [
  // 1. Groupe des pages ÉTUDIANT 
  {
    path: 'etudiant',
    component: EtudiantLayoutComponent, // Le parent avec la nav
    children: [
      { path: 'accueil', component: EtudiantAccueilComponent },
      {  path: 'profil', component: EtudiantProfilComponent },
      { path: 'chat', component: EtudiantChatComponent },
      { path: 'chat/conversation/:id', component: EtudiantConversationComponent },
      // Tu ajouteras tes futures pages ici, elles auront toutes la nav automatiquement !
      { path: '', redirectTo: 'accueil', pathMatch: 'full' }
    ]
  },

  // 2. ENSEIGNANT
  {
    path: 'enseignant',
    component: EnseignantLayout,
    children: [
    { path: 'accueil', component: EnseignantAccueilComponent },
    { path: 'mes-cours', component: MesCoursComponent },
    { path: 'nouveau-cours', component: NouveauCoursComponent },
    { path: 'conversations', component: ConversationsComponent },
    { path: 'profil', component: ProfilEnseignantComponent },
    { path: '', redirectTo: 'accueil', pathMatch: 'full' }
  ]
  },


  // Redirection par défaut
  { path: '', redirectTo: 'etudiant/accueil', pathMatch: 'full' }
];
