import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { Router, RouterModule } from '@angular/router';
import { EnseignantService } from '../../services/enseignant.service';
import { AuthService } from '../../../auth/services/auth.service';
import { LogService } from '../../../general/log/log.service';

@Component({
  selector: 'app-nouveau-cours',
  standalone: true,
  imports: [CommonModule, FormsModule, RouterModule],
  templateUrl: './nouveau-cours.component.html',
  styleUrls: ['./nouveau-cours.component.css']
})
export class NouveauCoursComponent implements OnInit {
  userId!: number;
  matieres: any[] = [];
  languesList: any[] = [];

  cours: any = {
    matiere: '',
    prix_heure: '',
    mode_cours: 'distanciel',
    camera_obligatoire: false,
    suivi: false,
    description: ''
  };

  languesSelectionnees: number[] = [];
  erreurs: string[] = [];

  constructor(
    private service: EnseignantService,
    private authService: AuthService,
    private logService: LogService,
    private router: Router
  ) {}

  ngOnInit() {
    const user = this.authService.getUtilisateurConnecte();
    if (!user || !user.id) {
      this.router.navigate(['/connexion']);
      return;
    }
    this.userId = user.id;

    this.service.getMatieres(this.userId).subscribe({
      next: (data) => this.matieres = data,
      error: (err) => console.error('Erreur chargement matières :', err)
    });

    this.service.getLangues(this.userId).subscribe({
      next: (data) => this.languesList = data,
      error: (err) => console.error('Erreur chargement langues :', err)
    });
  }

  toggleLangue(idLangue: number) {
    const idx = this.languesSelectionnees.indexOf(idLangue);
    if (idx > -1) {
      this.languesSelectionnees.splice(idx, 1);
    } else {
      this.languesSelectionnees.push(idLangue);
    }
  }

  onSubmit() {
    this.erreurs = [];

    if (!this.cours.matiere) {
      this.erreurs.push("La matière est obligatoire.");
    }
    if (this.languesSelectionnees.length === 0) {
      this.erreurs.push("Veuillez sélectionner au moins une langue.");
    }
    if (!this.cours.prix_heure || this.cours.prix_heure <= 0) {
      this.erreurs.push("Le prix par heure doit être supérieur à 0.");
    }
    if (!this.cours.description || this.cours.description.length < 20) {
      this.erreurs.push("La description doit contenir au moins 20 caractères.");
    }

    if (this.erreurs.length > 0) {
      return;
    }

    const payload = {
      matiere: this.cours.matiere,
      langues: this.languesSelectionnees,
      prix_heure: this.cours.prix_heure,
      mode_cours: this.cours.mode_cours,
      camera_obligatoire: this.cours.camera_obligatoire,
      suivi: this.cours.suivi,
      description: this.cours.description
    };

    this.service.creerCours(this.userId, payload).subscribe({
      next: (res) => {
        if (res.success) {
          alert('Cours créé avec succès !');

          this.logService.LogEvenement(
            'TEACHER_COURSE',
            'CREATE_COURSE',
            `Nouveau cours créé pour la matière ID: ${this.cours.matiere}`,
            'INFO',
            String(this.userId),
            {
              matiere: this.cours.matiere,
              suivi: this.cours.suivi,
              prix: this.cours.prix_heure
            }
          );

          this.router.navigate(['/enseignant/mes-cours']);
        } else {
          this.erreurs = res.errors || [res.message || 'Une erreur est survenue.'];
        }
      },
      error: (err) => {
        console.error('Erreur lors de la création du cours :', err);
        this.erreurs = [err.error?.errors?.[0] || 'Erreur de connexion au serveur.'];
      }
    });
  }
}
