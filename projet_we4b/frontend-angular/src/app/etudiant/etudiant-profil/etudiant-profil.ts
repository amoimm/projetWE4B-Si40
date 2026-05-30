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
  ancienMdp: string = '';
  nouveauMdp: string = '';
  confirmeMdp: string = '';
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

      // Sécurité côté client : on vérifie si l'utilisateur a tenté de remplir le nouveau mdp
      if (this.nouveauMdp || this.ancienMdp || this.confirmeMdp) {
        if (this.ancienMdp == this.nouveauMdp) {
          alert("Le nouveau mot de passe doit être différent de l'ancien !");
          return;
        }
        if (this.nouveauMdp !== this.confirmeMdp) {
          alert("Le nouveau mot de passe et sa confirmation ne correspondent pas !");
          return;
        }
        if (!this.ancienMdp) {
          alert("Veuillez renseigner votre ancien mot de passe pour valider le changement.");
          return;
        }
      }

      this.etudiantService.updateProfilEtudiant(7, {
        prenom: this.donneesEtudiant.prenom,
        nom: this.donneesEtudiant.nom,
        email: this.donneesEtudiant.email,
        ancienMdp: this.ancienMdp,
        nouveauMdp: this.nouveauMdp
      }).subscribe({
        next: (reponse) => {
          if (reponse.succes) {
            alert(reponse.message || 'Informations mises à jour avec succès !');
            this.enModeEdition = false;
            // On vide les champs de mot de passe par sécurité
            this.ancienMdp = '';
            this.nouveauMdp = '';
            this.confirmeMdp = '';
          } else {
            alert('Erreur : ' + reponse.message);
          }
        },
        error: (err) => console.error('Erreur modification profil :', err)
      });
    } else {
      this.enModeEdition = true;
    }
  }
}
