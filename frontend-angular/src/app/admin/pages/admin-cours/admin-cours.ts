import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { AdminService } from '../../services/admin.service';

@Component({
  selector: 'app-admin-cours',
  standalone: true,
  imports: [CommonModule, FormsModule],
  templateUrl: './admin-cours.html',
  styleUrl: './admin-cours.css',
})
export class AdminCours implements OnInit {
  cours: any[] = [];
  search = '';
  loading = true;
  error: string | null = null;
  successMessage: string | null = null;

  constructor(private adminService: AdminService) {}

  ngOnInit() {
    this.chargerCours();
  }

  // Charge la liste des cours
  chargerCours() {
    this.loading = true;
    this.error = null;
    this.successMessage = null;

    this.adminService.getCours(this.search).subscribe({
      next: (data) => {
        this.cours = data || [];
        this.loading = false;
      },
      error: (err) => {
        console.error('Erreur:', err);
        this.error = 'Erreur lors du chargement des cours';
        this.loading = false;
      }
    });
  }

  // Applique la recherche
  chercher() {
    this.chargerCours();
  }

  // Réinitialise la recherche
  reinitialiser() {
    this.search = '';
    this.chargerCours();
  }

  // Supprime un cours
  supprimerCours(idCours: number) {
    if (!confirm('Êtes-vous sûr de vouloir supprimer ce cours ?')) {
      return;
    }

    this.adminService.supprimerCours(idCours).subscribe({
      next: () => {
        this.successMessage = 'Cours supprimé avec succès.';
        setTimeout(() => this.successMessage = null, 3000);
        this.chargerCours();
      },
      error: (err) => {
        console.error('Erreur:', err);
        this.error = 'Erreur lors de la suppression';
      }
    });
  }
}
