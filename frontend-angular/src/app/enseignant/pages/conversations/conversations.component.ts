import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { EnseignantService } from '../../services/enseignant.service';
import { RouterModule } from '@angular/router';
import { AuthService } from '../../../auth/services/auth.service';

@Component({
  selector: 'app-conversations',
  standalone: true,
  imports: [CommonModule, FormsModule, RouterModule],
  templateUrl: './conversations.component.html',
  styleUrls: ['./conversations.component.css']
})
export class ConversationsComponent implements OnInit {
  monProfil: any = null;
  activeTab: 'active' | 'new' = 'active';
  
  // Données pour l'onglet "Actives"
  conversationsList: any[] = [];
  conversationsGrouped: any = {};
  
  // Données pour l'onglet "Cours"
  mesCours: any[] = [];
  selectedCours: any = null;
  elevesDuCours: any[] = [];

  messages: any[] = [];
  nouveauMessage: string = '';
  convActiveId: number | null = null;

  constructor(
    private service: EnseignantService,
    private authService: AuthService
  ) {}

  ngOnInit(): void {
    this.monProfil = this.authService.getUtilisateurConnecte();
    this.chargerDonnees();
  }

  chargerDonnees() {
    if (!this.monProfil) return;
    const userId = this.monProfil.id;

    // Chargement des conversations actives
    this.service.getConversations(userId).subscribe({
      next: (data) => {
        this.conversationsList = data;
        this.grouperConversations();
      },
      error: (err) => console.error('Erreur chargement convs :', err)
    });

    // Chargement des cours pour le 2ème onglet
    this.service.getCours(userId).subscribe({
      next: (data) => this.mesCours = data,
      error: (err) => console.error('Erreur chargement cours :', err)
    });
    
  }

  grouperConversations() {
    if (!this.monProfil) return;
    const userId = this.monProfil.id;

    this.conversationsGrouped = this.conversationsList.reduce((acc, conv) => {
      (acc[conv.cours] = acc[conv.cours] || []).push(conv);
      return acc;
    }, {});
  }

  selectCours(cours: any) {
    if (!this.monProfil) return;
    const userId = this.monProfil.id;

    this.selectedCours = cours;
    this.elevesDuCours = [];
    
    this.service.getElevesParCours(userId, cours.id_cours).subscribe({
        next: (data) => this.elevesDuCours = data,
        error: (err) => console.error(err)
    });
  }

  selectionnerConv(id: number) {
    if (!this.monProfil) return;
    const userId = this.monProfil.id;

    this.convActiveId = id;
    this.service.getMessages(userId, id).subscribe({
      next: (data) => this.messages = data,
      error: (err) => console.error('Erreur chargement messages :', err)
    });
  }
}