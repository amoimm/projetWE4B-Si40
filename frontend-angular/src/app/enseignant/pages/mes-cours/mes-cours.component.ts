import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { RouterModule } from '@angular/router';
import { EnseignantService } from '../../services/enseignant.service';
import { AuthService } from '../../../auth/services/auth.service';

@Component({
  selector: 'app-mes-cours',
  standalone: true,
  imports: [CommonModule, FormsModule, RouterModule],
  templateUrl: './mes-cours.component.html',
  styleUrls: ['./mes-cours.component.css']
})
export class MesCoursComponent implements OnInit {
  monProfil: any = null;
  coursList: any[] = [];
  matieres: any[] = [];
  languesList: any[] = [];

  recherche: string = '';
  filtreMatiere: string = '';
  filtreLangue: string = '';
  filtreAvis: string = '';

  constructor(
    private enseignantService: EnseignantService,
    private authService: AuthService
  ) {}

  ngOnInit(): void {
    this.monProfil = this.authService.getUtilisateurConnecte();
    console.log("Profil chargé dans mes-cours :", this.monProfil);
    
    this.chargerFiltres();
    this.chargerCours();
  }

  chargerFiltres() {
    if (!this.monProfil) return;
  
    const userId = this.monProfil.id;
    
    this.enseignantService.getMatieres(userId).subscribe({
      next: (data) => this.matieres = data,
      error: (err) => console.error('Erreur chargement matières :', err)
    });

    // Récupérer les langues pour le dropdown
    this.enseignantService.getLangues(userId).subscribe({
      next: (data) => this.languesList = data,
      error: (err) => console.error('Erreur chargement langues :', err)
    });
  }

  chargerCours() {
    if (!this.monProfil) return;
    const userId = this.monProfil.id;

    const params: any = {};
    if (this.recherche.trim()) {
      params.recherche = this.recherche.trim();
    }
    if (this.filtreMatiere) {
      params.matiere = this.filtreMatiere;
    }
    if (this.filtreLangue) {
      params.langue = this.filtreLangue;
    }
    if (this.filtreAvis) {
      params.avis = this.filtreAvis;
    }

    this.enseignantService.getCours(userId, params).subscribe({
      next: (data) => {
        this.coursList = data;
      },
      error: (err) => console.error('Erreur chargement cours :', err)
    });
  }

  supprimerCours(id: number) {
    if (confirm('Voulez-vous vraiment supprimer ce cours ?')) {
      this.enseignantService.supprimerCours(id).subscribe({
        next: (res) => {
          if (res.success) {
            alert(res.message);
            this.chargerCours();
          } else {
            alert('Erreur : ' + res.message);
          }
        },
        error: (err) => {
          console.error('Erreur suppression cours :', err);
          alert('Erreur lors de la suppression.');
        }
      });
    }
  }
}
