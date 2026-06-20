import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterLink } from '@angular/router';
import { EtudiantService } from '../../services/etudiant.service';
import { interval, Subscription } from 'rxjs';
import { AuthService } from '../../../auth/services/auth.service';

@Component({
  selector: 'app-etudiant-chat',
  standalone: true,
  imports: [CommonModule, RouterLink],
  templateUrl: './etudiant-chat.html',
  styleUrls: ['./etudiant-chat.css']
})
export class EtudiantChatComponent implements OnInit {
  conversations: any[] = [];

  private actualisationAuto!: Subscription;
  monProfil: any = null;
  constructor(
    private etudiantService: EtudiantService,
    private authService: AuthService
  ) {}

  ngOnInit(): void {
    this.monProfil = this.authService.getUtilisateurConnecte();

    this.chargerConversations();

    this.actualisationAuto = interval(2000).subscribe(() => {
      this.chargerConversations();
    });
  }

  
  ngOnDestroy(): void {
    if (this.actualisationAuto) {
      this.actualisationAuto.unsubscribe();
    }
  }

  chargerConversations(): void {
    this.etudiantService.getConversations(this.monProfil.id).subscribe({
      next: (data) => {
        this.conversations = data;
      },
      error: (err) => {
        console.error('Erreur lors du chargement des conversations :', err);
      }
    });
  }
}
