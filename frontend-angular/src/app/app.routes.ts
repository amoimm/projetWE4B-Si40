import { Routes } from '@angular/router';

import { EtudiantLayoutComponent } from './etudiant/components/etudiant-layout/etudiant-layout';
import { EtudiantAccueilComponent } from './etudiant/pages/etudiant-accueil/etudiant-accueil';
import { EtudiantChatComponent } from './etudiant/pages/etudiant-chat/etudiant-chat';
import { EtudiantConversationComponent } from './etudiant/pages/etudiant-conversation/etudiant-conversation';
import { EtudiantDevenirProfComponent } from './etudiant/pages/etudiant-devenir-prof/etudiant-devenir-prof';



import { EnseignantLayout } from './enseignant/components/enseignant-layout/enseignant-layout';
import { EnseignantAccueilComponent } from './enseignant/pages/enseignant-accueil/enseignant-accueil.component';
import { MesCoursComponent } from './enseignant/pages/mes-cours/mes-cours.component';
import { NouveauCoursComponent } from './enseignant/pages/nouveau-cours/nouveau-cours.component';
import { ModifCoursComponent } from './enseignant/pages/modif-cours/modif-cours.component';
import { ConversationsComponent } from './enseignant/pages/conversations/conversations.component';
import { MessagerieComponent } from './enseignant/pages/messagerie/messagerie.component';

import { AdminLayout } from './admin/components/admin-layout/admin-layout';
import { AdminAccueil } from './admin/pages/admin-accueil/admin-accueil';
import { AdminUtilisateurs } from './admin/pages/admin-utilisateurs/admin-utilisateurs';
import { AdminCours } from './admin/pages/admin-cours/admin-cours';
import { AdminConfig } from './admin/pages/admin-config/admin-config';

import { ProfilComponent } from './general/pages/profil/profil';
import { AccueilComponent } from './general/pages/accueil/accueil';

import {ConnexionComponent} from './auth/pages/connexion/connexion'
import {InscriptionComponent} from './auth/pages/inscription/inscription';
import { MotDePasseOublieComponent } from './auth/pages/mot-de-passe-oublie/mot-de-passe-oublie';

import { roleGuard } from './general/guards/auth.guard';
export const routes: Routes = [
  { path: 'accueil', component: AccueilComponent },

  // 1. Groupe des pages ÉTUDIANT
  {
    path: 'etudiant',
    component: EtudiantLayoutComponent,
    canActivate: [roleGuard(['etudiant', 'professeur', 'admin'])],
    children: [
      { path: 'accueil', component: EtudiantAccueilComponent },
      {  path: 'profil', component: ProfilComponent },
      { path: 'chat', component: EtudiantChatComponent },
      { path: 'chat/conversation/:id', component: EtudiantConversationComponent },
      { path: 'devenir-prof', component: EtudiantDevenirProfComponent },

      { path: '', redirectTo: 'accueil', pathMatch: 'full' }
    ]
  },

  // 2. ENSEIGNANT
  {
    path: 'enseignant',
    component: EnseignantLayout,
    canActivate: [roleGuard(['professeur', 'admin'])],
    children: [
    { path: 'accueil', component: EnseignantAccueilComponent },
    { path: 'mes-cours', component: MesCoursComponent },
    { path: 'mes-cours/modif-cours/:id', component: ModifCoursComponent },
    { path: 'nouveau-cours', component: NouveauCoursComponent },
    { path: 'conversations', component: ConversationsComponent },
    { path: 'conversations/messagerie', component: MessagerieComponent },
    { path: 'profil', component: ProfilComponent },
    { path: '', redirectTo: 'accueil', pathMatch: 'full' }
  ]
  },

  // 3. ADMIN
  {
    path: 'admin',
    component: AdminLayout,
    canActivate: [roleGuard(['admin'])],
    children: [
      { path: 'accueil', component: AdminAccueil },
      { path: 'utilisateurs', component: AdminUtilisateurs },
      { path: 'cours', component: AdminCours },
      { path: 'config', component: AdminConfig },
      { path: 'profil', component: ProfilComponent },
      { path: '', redirectTo: 'accueil', pathMatch: 'full' }
    ]
  },

  {
    path: 'auth',
    children: [
      { path: 'connexion', component: ConnexionComponent },
      { path: 'inscription', component: InscriptionComponent},
      { path: 'mot-de-passe-oublie', component: MotDePasseOublieComponent }
    ]
  },

  // Redirection par défaut
  { path: '', redirectTo: 'accueil', pathMatch: 'full' },
];
