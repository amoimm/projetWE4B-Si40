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
  // 1. Déclaration de TOUTES les variables utilisées dans le [(ngModel)] du HTML
  recherche: string = '';
  prixMax: number | null = null;
  filtreMatiere: string = '';
  filtreLangue: string = '';
  filtreMode: string = '';
  filtreSuivi: string = '';
  filtreAvis: string = '';

  // 2. Les tableaux de données pour remplir les <option> de tes menus déroulants
  matieres = [
    { id_matiere: '1', nom: 'LO43 - Bases de données' },
    { id_matiere: '2', nom: 'WE4B - Applications Web' },
    { id_matiere: '3', nom: 'LE01 - Anglais' }
  ];

  langues = [
    { id_langue: '1', nom: 'Français' },
    { id_langue: '2', nom: 'Anglais' }
  ];

  // 3. Le tableau qui recevra les vrais cours de la BDD
  coursFiltres: any[] = [];

  constructor(private etudiantService: EtudiantService) {}

  ngOnInit(): void {
    this.appliquerFiltrage();
  }

  appliquerFiltrage(): void {
    // Pour l'instant on envoie la recherche et le prix au service.
    // Tu pourras ajouter les autres filtres (matière, langue) plus tard dans ton service PHP si tu le souhaites !
    this.etudiantService.rechercherCours(this.recherche, this.prixMax).subscribe({
      next: (donnees) => {
        // Optionnel : Si ton PHP n'est pas encore prêt, tu peux temporairement mettre des données en dur ici pour tester le visuel
        this.coursFiltres = donnees || [];
      },
      error: (erreur) => {
        console.error('Erreur SQL via le service :', erreur);
      }
    });
  }
}
