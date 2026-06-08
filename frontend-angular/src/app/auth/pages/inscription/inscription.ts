import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { Router, RouterLink } from '@angular/router';
import { AuthService } from '../../services/auth.service';
import { EtudiantService } from '../../../etudiant/services/etudiant.service';

@Component({
  selector: 'app-inscription',
  standalone: true,
  imports: [CommonModule, FormsModule, RouterLink],
  templateUrl: './inscription.html',
  styleUrls: ['./inscription.css']
})
export class InscriptionComponent implements OnInit {
  role: string = 'etudiant';
  nom: string = '';
  prenom: string = '';
  email: string = '';
  motDePasse: string = '';
  confirmation: string = '';
  presentation: string = '';

  listeMatieres: any[] = [];
  listeLangues: any[] = [];
  matieresSelectionnees: Set<string> = new Set();
  languesSelectionnees: Set<string> = new Set();
  fichiersCertif: File[] = [];

  messageErreur: string = '';
  messageSucces: string = '';

  constructor(
    private authService: AuthService,
    private etudiantService: EtudiantService,
    private router: Router
  ) {}

  ngOnInit(): void {
    this.etudiantService.getFiltresDisponibles().subscribe({
      next: (data) => {
        this.listeMatieres = data.matieres;
        this.listeLangues = data.langues;
      }
    });
  }

  selectionnerRole(nouveauRole: string): void {
    this.role = nouveauRole;
  }

  toggleMatiere(nom: string) {
    this.matieresSelectionnees.has(nom) ? this.matieresSelectionnees.delete(nom) : this.matieresSelectionnees.add(nom);
  }
  toggleLangue(nom: string) {
    this.languesSelectionnees.has(nom) ? this.languesSelectionnees.delete(nom) : this.languesSelectionnees.add(nom);
  }
  onFichiersSelectionnes(event: any) {
    if (event.target.files.length > 0) {
      this.fichiersCertif = Array.from(event.target.files);
    }
  }

  sInscrire(): void {
    if (!this.nom || !this.prenom || !this.email || !this.motDePasse || !this.confirmation) {
      this.messageErreur = 'Veuillez remplir tous les champs personnels.';
      return;
    }
    if (this.motDePasse !== this.confirmation) {
      this.messageErreur = 'Les mots de passe ne correspondent pas.';
      return;
    }

    if (this.role === 'enseignant') {
      if (this.matieresSelectionnees.size === 0 || this.languesSelectionnees.size === 0) {
        this.messageErreur = 'Un professeur doit sélectionner au moins une matière et une langue.';
        return;
      }
      if (this.fichiersCertif.length === 0) {
        this.messageErreur = 'Veuillez fournir au moins un certificat au format PDF.';
        return;
      }
    }

    this.messageErreur = '';

    const formData = new FormData();
    formData.append('role', this.role);
    formData.append('nom', this.nom);
    formData.append('prenom', this.prenom);
    formData.append('email', this.email);
    formData.append('password', this.motDePasse);
    formData.append('presentation', this.presentation);

    if (this.role === 'enseignant') {
      formData.append('matieres', JSON.stringify(Array.from(this.matieresSelectionnees)));
      formData.append('langues', JSON.stringify(Array.from(this.languesSelectionnees)));
      this.fichiersCertif.forEach((fichier) => {
        formData.append('certificats[]', fichier, fichier.name);
      });
    }

    this.authService.inscription(formData).subscribe({
      next: (reponse: any) => {
        if (reponse.succes) {
          this.messageSucces = reponse.message;

          if (reponse.utilisateur) {
            this.authService.sauvegarderSession(reponse.utilisateur);
          }

          setTimeout(() => {
            if (reponse.utilisateur.role === 'admin') {
              this.router.navigate(['/admin/accueil']);
            } else if (reponse.utilisateur.role === 'enseignant') {
              this.router.navigate(['/enseignant/accueil']);
            } else {
              this.router.navigate(['/etudiant/accueil']);
            }
          }, 1500);

        } else {
          this.messageErreur = reponse.message;
        }
      },
      error: (err: any) => this.messageErreur = 'Erreur serveur.'
    });
  }
}
