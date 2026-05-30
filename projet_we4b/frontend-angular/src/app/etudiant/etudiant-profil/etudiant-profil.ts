import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { EtudiantService } from './etudiant.service';

@Component({
  selector: 'app-etudiant-profil',
  standalone: true,
  imports: [CommonModule, FormsModule],
  templateUrl: './etudiant-profil.html',
  styleUrls: ['./etudiant-profil.css']
})
export class EtudiantProfilComponent implements OnInit {
  donneesEtudiant: any = null;
  enModeEdition: boolean = false;

  constructor(private etudiantService: EtudiantService) { }

  ngOnInit(): void {
    // Ton code actuel pour charger l'étudiant au démarrage (ex: ID 8)
    this.etudiantService.getProfilEtudiant(7).subscribe(data => {
      this.donneesEtudiant = data;
    });
  }

  changerTheme(nouveauTheme: string): void {
    const userId = 7;

    this.etudiantService.updateProfilEtudiant(userId, { theme: nouveauTheme }).subscribe({
      next: (reponse) => {
        console.log('Serveur PHP :', reponse);
        if (this.donneesEtudiant) {
          this.donneesEtudiant.theme = nouveauTheme;
        }
        alert('Préférence enregistrée en BDD : Thème ' + nouveauTheme);
      },
      error: (err) => {
        console.error('Erreur lors de la modification :', err);
      }
    });
  }

  validerModifications(): void {
    if (this.enModeEdition) {
      this.etudiantService.updateProfilEtudiant(7, {
        prenom: this.donneesEtudiant.prenom,
        nom: this.donneesEtudiant.nom,
        email: this.donneesEtudiant.email
      }).subscribe({
        next: (reponse) => {
          alert('Informations mises à jour en BDD avec succès !');
          this.enModeEdition = false;
        },
        error: (err) => console.error('Erreur modification profil :', err)
      });
    } else {
      this.enModeEdition = true;
    }
  }
}
