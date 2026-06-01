import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterLink } from '@angular/router';
import { EtudiantService } from '../../services/etudiant.service';
import { interval, Subscription } from 'rxjs';

@Component({
  selector: 'app-etudiant-chat',
  standalone: true,
  imports: [CommonModule, RouterLink],
  templateUrl: './etudiant-chat.html',
  styleUrls: ['./etudiant-chat.css']
})
export class EtudiantChatComponent implements OnInit {
  conversations: any[] = [];
  idEtudiantTest = 8;

  private actualisationAuto!: Subscription;
  constructor(private etudiantService: EtudiantService) {}

  ngOnInit(): void {
    // 1. On charge la liste immédiatement au premier affichage de la page
    this.chargerConversations();

    // 2. On lance la boucle temporelle silencieuse (toutes les 2000 ms = 2 secondes)
    this.actualisationAuto = interval(2000).subscribe(() => {
      this.chargerConversations();
    });
  }

  // 🌟 TRÈS IMPORTANT : On coupe la boucle quand on quitte la page
  ngOnDestroy(): void {
    if (this.actualisationAuto) {
      this.actualisationAuto.unsubscribe();
    }
  }

  chargerConversations(): void {
    // Fait appel à la route de ton service qui contacte api-conversations.php
    this.etudiantService.getConversations(this.idEtudiantTest).subscribe({
      next: (data) => {
        this.conversations = data;
      },
      error: (err) => {
        console.error('Erreur lors du chargement des conversations :', err);
      }
    });
  }
}
