import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { AdminService } from '../../services/admin.service';

@Component({
  selector: 'app-admin-utilisateurs',
  standalone: true,
  imports: [CommonModule, FormsModule],
  templateUrl: './admin-utilisateurs.html',
  styleUrl: './admin-utilisateurs.css',
})
export class AdminUtilisateurs implements OnInit {
  utilisateurs: any[] = [];
  totalCount = 0;
  currentPage = 1;
  perPage = 15;
  totalPages = 0;

  // Filtres
  search = '';
  rangFilter = '';

  // États
  loading = true;
  error: string | null = null;
  successMessage: string | null = null;

  // Pour la modification du rang
  selectedRangs: { [key: number]: number } = {};

  constructor(private adminService: AdminService) {}

  ngOnInit() {
    this.chargerUtilisateurs();
  }

  // Charge la liste des utilisateurs
  chargerUtilisateurs() {
    this.loading = true;
    this.error = null;
    this.successMessage = null;

    this.adminService.getUtilisateurs(this.search, this.rangFilter, this.currentPage).subscribe({
      next: (data) => {
        this.utilisateurs = data.users || [];
        this.totalCount = data.total || 0;
        this.totalPages = data.total_pages || 0;
        this.loading = false;

        // Initialiser les rangs sélectionnés
        this.utilisateurs.forEach(u => {
          this.selectedRangs[u.id_utilisateurs] = u.rang;
        });
      },
      error: (err) => {
        console.error('Erreur:', err);
        this.error = 'Erreur lors du chargement des utilisateurs';
        this.loading = false;
      }
    });
  }

  // Appliquer les filtres et recherche
  filtrer() {
    this.currentPage = 1;
    this.chargerUtilisateurs();
  }

  // Réinitialiser les filtres
  reinitialiser() {
    this.search = '';
    this.rangFilter = '';
    this.currentPage = 1;
    this.chargerUtilisateurs();
  }

  // Modifier le rang d'un utilisateur
  modifierRang(idUtilisateur: number) {
    const newRang = this.selectedRangs[idUtilisateur];

    this.adminService.modifierRangUtilisateur(idUtilisateur, newRang).subscribe({
      next: () => {
        this.successMessage = 'Rang mis à jour avec succès.';
        setTimeout(() => this.successMessage = null, 3000);
      },
      error: (err) => {
        console.error('Erreur:', err);
        this.error = 'Erreur lors de la modification du rang';
      }
    });
  }

  // Supprimer un utilisateur
  supprimerUtilisateur(idUtilisateur: number) {
    if (!confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?')) {
      return;
    }

    this.adminService.supprimerUtilisateur(idUtilisateur).subscribe({
      next: () => {
        this.successMessage = 'Utilisateur supprimé avec succès.';
        setTimeout(() => this.successMessage = null, 3000);
        this.chargerUtilisateurs();
      },
      error: (err) => {
        console.error('Erreur:', err);
        this.error = 'Erreur lors de la suppression';
      }
    });
  }

  // Aller à une page spécifique
  allerPage(page: number) {
    if (page >= 1 && page <= this.totalPages) {
      this.currentPage = page;
      this.chargerUtilisateurs();
    }
  }

  // Obtenir un array de pages pour l'affichage de la pagination
  getPagesArray(): number[] {
    const pages = [];
    for (let i = 1; i <= this.totalPages; i++) {
      pages.push(i);
    }
    return pages;
  }

  // Retourne le texte du rôle
  getRoleTexte(rang: number): string {
    switch(rang) {
      case 0: return 'Étudiant';
      case 1: return 'Professeur';
      case 2: return 'Admin';
      default: return 'Inconnu';
    }
  }
}
