import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { RouterLink } from '@angular/router';
import { EtudiantService } from '../../services/etudiant.service';

@Component({
  selector: 'app-etudiant-accueil',
  standalone: true,
  imports: [CommonModule, FormsModule, RouterLink],
  templateUrl: './etudiant-accueil.html',
  styleUrls: ['./etudiant-accueil.css']
})
export class EtudiantAccueilComponent implements OnInit {
  // Variables des filtres
  recherche: string = '';
  prixMax: number | null = null;
  filtreMatiere: string = '';
  filtreLangue: string = '';
  filtreMode: string = '';
  filtreSuivi: string = '';
  filtreAvis: string = '';

  // Tableaux dynamiques qui vont se remplir avec ta BDD
  matieres: any[] = [];
  langues: any[] = [];
  coursFiltres: any[] = [];

  constructor(private etudiantService: EtudiantService) {}

  ngOnInit(): void {
    // 1. On charge les langues et matières depuis la BDD
    this.etudiantService.getFiltresDisponibles().subscribe({
      next: (data) => {
        this.matieres = data.matieres;
        this.langues = data.langues;
      },
      error: (err) => console.error('Erreur chargement des filtres:', err)
    });

    // 2. On affiche les cours par défaut
    this.appliquerFiltrage();
  }

  appliquerFiltrage(): void {
    // On regroupe tous les choix de l'étudiant dans un seul objet
    const tousLesFiltres = {
      recherche: this.recherche,
      prixMax: this.prixMax,
      filtreMatiere: this.filtreMatiere,
      filtreLangue: this.filtreLangue,
      filtreMode: this.filtreMode,
      filtreSuivi: this.filtreSuivi,
      filtreAvis: this.filtreAvis
    };

    // On envoie le tout au service
    this.etudiantService.rechercherCours(tousLesFiltres).subscribe({
      next: (donnees) => {
        this.coursFiltres = donnees || [];
      },
      error: (erreur) => {
        console.error('Erreur SQL via le service :', erreur);
      }
    });
  }
}
