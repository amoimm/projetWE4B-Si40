import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { RouterLink } from '@angular/router';
import { EtudiantService } from '../../services/etudiant.service';
import { AuthService } from '../../../auth/services/auth.service';
import {LogService} from '../../../general/log/log.service';

@Component({
  selector: 'app-etudiant-accueil',
  standalone: true,
  imports: [CommonModule, FormsModule, RouterLink],
  templateUrl: './etudiant-accueil.html',
  styleUrls: ['./etudiant-accueil.css']
})
export class EtudiantAccueilComponent implements OnInit {
  monProfil: any = null;

  recherche: string = '';
  prixMax: number | null = null;
  filtreMatiere: string = '';
  filtreLangue: string = '';
  filtreMode: string = '';
  filtreSuivi: string = '';
  filtreAvis: string = '';

  matieres: any[] = [];
  langues: any[] = [];
  coursFiltres: any[] = [];

  constructor(
    private etudiantService: EtudiantService,
    private logService: LogService,
    private authService: AuthService
  ) {}

  ngOnInit(): void {
    this.monProfil = this.authService.getUtilisateurConnecte();
    this.etudiantService.getFiltresDisponibles().subscribe({
      next: (data) => {
        this.matieres = data.matieres;
        this.langues = data.langues;
      },
      error: (err) => console.error('Erreur chargement des filtres:', err)
    });

    this.appliquerFiltrage();
  }

  appliquerFiltrage(): void {
    const tousLesFiltres = {
      recherche: this.recherche,
      prixMax: this.prixMax,
      filtreMatiere: this.filtreMatiere,
      filtreLangue: this.filtreLangue,
      filtreMode: this.filtreMode,
      filtreSuivi: this.filtreSuivi,
      filtreAvis: this.filtreAvis
    };

    this.etudiantService.rechercherCours(tousLesFiltres).subscribe({
      next: (donnees) => {
        this.coursFiltres = donnees || [];
      },
      error: (erreur) => {
        console.error('Erreur SQL via le services :', erreur);
      }
    });

    this.logService.LogEvenement(
      'STUDENT_SEARCH',
      'APPLY_FILTERS',
      `L'élève a filtré les cours`,
      'INFO',
      this.monProfil.id,
      {
        recherche: this.recherche,
        matiere: this.filtreMatiere,
        langue: this.filtreLangue,
        prix_max: this.prixMax,
        mode: this.filtreMode
      }
    );
  }
}
