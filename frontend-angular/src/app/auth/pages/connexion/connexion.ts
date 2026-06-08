import { Component } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { Router, RouterLink } from '@angular/router';
import { AuthService } from '../../services/auth.service';

@Component({
  selector: 'app-connexion',
  standalone: true,
  imports: [CommonModule, FormsModule, RouterLink],
  templateUrl: './connexion.html',
  styleUrls: ['./connexion.css']
})
export class ConnexionComponent {
  email: string = '';
  motDePasse: string = '';
  messageErreur: string = '';

  constructor(private authService: AuthService, private router: Router) {}

  seConnecter(): void {
    if (!this.email || !this.motDePasse) {
      this.messageErreur = 'Veuillez renseigner tous les champs.';
      return;
    }

    this.messageErreur = '';

    this.authService.connexion(this.email, this.motDePasse).subscribe({
      next: (reponse) => {
        if (reponse.succes) {
          this.authService.sauvegarderSession(reponse.utilisateur);

          if (reponse.utilisateur.role === 'admin') {
            this.router.navigate(['/admin/accueil']);
          } else if (reponse.utilisateur.role === 'enseignant') {
            this.router.navigate(['/enseignant/accueil']);
          } else {
            this.router.navigate(['/etudiant/accueil']);
          }
        } else {
          this.messageErreur = reponse.message;
        }
      },
      error: (err) => {
        console.error('Erreur API :', err);
        this.messageErreur = 'Erreur de connexion au serveur.';
      }
    });
  }
}
