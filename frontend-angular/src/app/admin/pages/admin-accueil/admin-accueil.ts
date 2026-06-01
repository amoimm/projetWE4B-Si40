import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { AdminService } from '../../services/admin.service';

@Component({
  selector: 'app-admin-accueil',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './admin-accueil.html',
  styleUrl: './admin-accueil.css'
})
export class AdminAccueil implements OnInit {
  // Variables pour stocker les données
  stats = {
    users: 0,
    cours: 0,
    messages: 0
  };

  utilisateursRecents: any[] = [];
  loading = true;
  error: string | null = null;

  constructor(private adminService: AdminService) {}

  ngOnInit() {
    this.chargerDonnees();
  }

  // Charge les stats et les utilisateurs récents
  chargerDonnees() {
    this.loading = true;
    this.error = null;

    // Requête 1 : Récupérer les stats
    this.adminService.getStats().subscribe({
      next: (data) => {
        this.stats = data;
      },
      error: (err) => {
        console.error('Erreur stats:', err);
        this.error = 'Erreur lors du chargement des statistiques';
      }
    });

    // Requête 2 : Récupérer les utilisateurs récents
    this.adminService.getUtilisateursRecents().subscribe({
      next: (data) => {
        this.utilisateursRecents = data;
        this.loading = false;
      },
      error: (err) => {
        console.error('Erreur utilisateurs:', err);
        this.error = 'Erreur lors du chargement des utilisateurs';
        this.loading = false;
      }
    });
  }

  // Retourne le badge de rôle approprié
  getBadgeRang(rang: number): { classe: string; texte: string } {
    switch(rang) {
      case 0: return { classe: 'badge-student', texte: 'Étudiant' };
      case 1: return { classe: 'badge-prof', texte: 'Professeur' };
      case 2: return { classe: 'badge-admin', texte: 'Admin' };
      default: return { classe: 'badge-other', texte: 'Inconnu' };
    }
  }
}
