import { Component } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { Router, RouterLink } from '@angular/router';
import { AuthService } from '../../services/auth.service';

@Component({
  selector: 'app-mot-de-passe-oublie',
  standalone: true,
  imports: [CommonModule, FormsModule, RouterLink],
  templateUrl: './mot-de-passe-oublie.html',
  styleUrls: ['./mot-de-passe-oublie.css']
})
export class MotDePasseOublieComponent {
  email: string = '';
  nouveauMdp: string = '';
  confirmeMdp: string = '';
  messageSucces: string = '';
  messageErreur: string = '';
  enCours: boolean = false;

  constructor(private authService: AuthService, private router: Router) {}

  envoyerDemande(): void {
    if (!this.email || !this.nouveauMdp || !this.confirmeMdp) {
      this.messageErreur = 'Veuillez remplir tous les champs.';
      this.messageSucces = '';
      return;
    }

    if (this.nouveauMdp !== this.confirmeMdp) {
      this.messageErreur = 'Le nouveau mot de passe et sa confirmation ne correspondent pas.';
      this.messageSucces = '';
      return;
    }

    this.messageErreur = '';
    this.messageSucces = '';
    this.enCours = true;

    this.authService.reinitialiserMdp(this.email, this.nouveauMdp).subscribe({
      next: (reponse) => {
        this.enCours = false;
        if (reponse.succes) {
          this.messageSucces = reponse.message;
          // Rediriger vers la connexion après 2 secondes
          setTimeout(() => {
            this.router.navigate(['/auth/connexion']);
          }, 2000);
        } else {
          this.messageErreur = reponse.message;
        }
      },
      error: (err) => {
        this.enCours = false;
        console.error('Erreur API :', err);
        this.messageErreur = 'Erreur de connexion au serveur.';
      }
    });
  }
}
