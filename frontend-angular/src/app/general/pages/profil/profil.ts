import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { ProfilServices } from '../../services/profil.services';
import { AuthService } from '../../../auth/services/auth.service';

@Component({
  selector: 'app-profil',
  standalone: true,
  imports: [CommonModule, FormsModule],
  templateUrl: './profil.html',
  styleUrls: ['./profil.css']
})
export class ProfilComponent implements OnInit {
  donneesUtilisateur: any = null;
  enModeEdition: boolean = false;
  ancienMdp: string = '';
  nouveauMdp: string = '';
  confirmeMdp: string = '';

  idUtilisateurConnecte: number = 0;
  roleUtilisateurConnecte: string = '' ;

  constructor(
    private profilService: ProfilServices,
    private authService: AuthService

  ) { }

  ngOnInit(): void {
    const user = this.authService.getUtilisateurConnecte();
    if (user){
      this.idUtilisateurConnecte = user.id;
      this.roleUtilisateurConnecte = user.role;
    }else{
      console.warn("Aucun utilisateur n'est connecté");
    }

    this.profilService.getProfil(this.idUtilisateurConnecte, this.roleUtilisateurConnecte).subscribe({
      next: (data) => {
        this.donneesUtilisateur = data;
      },
      error: (err) => console.error('Erreur de chargement du profil :', err)
    });
  }
  validerModifications(): void {
    if (this.enModeEdition) {
      // Vérifications de sécurité pour le mot de passe
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

      // On envoie les données de mise à jour au services global
      this.profilService.updateProfil(this.idUtilisateurConnecte, this.roleUtilisateurConnecte, {
        prenom: this.donneesUtilisateur.prenom,
        nom: this.donneesUtilisateur.nom,
        email: this.donneesUtilisateur.email,
        presentation: this.donneesUtilisateur.presentation,
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
