import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { RouterLink } from '@angular/router';
import { HttpClient } from '@angular/common/http'; // <-- Import indispensable

@Component({
  selector: 'app-etudiant-accueil',
  standalone: true,
  imports: [CommonModule, FormsModule, RouterLink],
  templateUrl: './etudiant-accueil.html',
  styleUrls: ['./etudiant-accueil.css']
})
export class EtudiantAccueilComponent implements OnInit {
  recherche: string = '';
  prixMax: number | null = null;

  // Tableau vide au départ, il va recevoir la BDD
  coursFiltres: any[] = [];

  // URL vers ton script PHP (ajuste le chemin selon ton dossier XAMPP)
  private apiUrl = 'http://localhost/projet_we4b/api-cours.php';

  // On injecte le client HTTP d'Angular dans le constructeur
  constructor(private http: HttpClient) {}

  ngOnInit(): void {
    this.chargerCoursDepuisBDD();
  }

  // Cette fonction interroge ton PHP qui lui-même interroge MySQL
  chargerCoursDepuisBDD(): void {
    // On construit les paramètres de filtres pour l'URL (?recherche=...&prix_max=...)
    const urlFiltree = `${this.apiUrl}?recherche=${this.recherche}&prix_max=${this.prixMax || ''}`;

    this.http.get<any[]>(urlFiltree).subscribe({
      next: (donnees) => {
        this.coursFiltres = donnees; // Tes cours de la BDD arrivent directement dans ton HTML !
      },
      error: (erreur) => {
        console.error('Erreur lors de la récupération des cours :', erreur);
      }
    });
  }

  // Dès qu'on tape dans la barre de recherche, on relance la requête SQL
  appliquerFiltrage(): void {
    this.chargerCoursDepuisBDD();
  }
}
