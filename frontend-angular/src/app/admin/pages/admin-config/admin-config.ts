import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { AdminService } from '../../services/admin.service';

@Component({
  selector: 'app-admin-config',
  standalone: true,
  imports: [CommonModule, FormsModule],
  templateUrl: './admin-config.html',
  styleUrl: './admin-config.css',
})
export class AdminConfig implements OnInit {
  // Matières
  matieres: any[] = [];
  newMatiere = '';

  // Langues
  langues: any[] = [];
  newLangue = '';

  // États
  loading = true;
  error: string | null = null;
  successMessage: string | null = null;

  constructor(private adminService: AdminService) {}

  ngOnInit() {
    this.chargerDonnees();
  }

  // Charge les matières et langues
  chargerDonnees() {
    this.loading = true;
    this.error = null;

    // Charger les matières
    this.adminService.getMatieres().subscribe({
      next: (data) => {
        this.matieres = data || [];
      },
      error: (err) => {
        console.error('Erreur matieres:', err);
        this.error = 'Erreur lors du chargement des matières';
      }
    });

    // Charger les langues
    this.adminService.getLangues().subscribe({
      next: (data) => {
        this.langues = data || [];
        this.loading = false;
      },
      error: (err) => {
        console.error('Erreur langues:', err);
        this.error = 'Erreur lors du chargement des langues';
        this.loading = false;
      }
    });
  }

  // Ajoute une nouvelle matière
  ajouterMatiere() {
    if (!this.newMatiere.trim()) {
      alert('Veuillez entrer un nom de matière');
      return;
    }

    this.adminService.ajouterMatiere(this.newMatiere.trim()).subscribe({
      next: () => {
        this.successMessage = 'Matière ajoutée avec succès.';
        setTimeout(() => this.successMessage = null, 3000);
        this.newMatiere = '';
        this.chargerDonnees();
      },
      error: (err) => {
        console.error('Erreur:', err);
        this.error = 'Erreur lors de l\'ajout de la matière';
      }
    });
  }

  // Ajoute une nouvelle langue
  ajouterLangue() {
    if (!this.newLangue.trim()) {
      alert('Veuillez entrer un nom de langue');
      return;
    }

    this.adminService.ajouterLangue(this.newLangue.trim()).subscribe({
      next: () => {
        this.successMessage = 'Langue ajoutée avec succès.';
        setTimeout(() => this.successMessage = null, 3000);
        this.newLangue = '';
        this.chargerDonnees();
      },
      error: (err) => {
        console.error('Erreur:', err);
        this.error = 'Erreur lors de l\'ajout de la langue';
      }
    });
  }
}
