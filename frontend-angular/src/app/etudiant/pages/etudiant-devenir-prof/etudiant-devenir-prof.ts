import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { Router } from '@angular/router';
import { EtudiantService } from '../../services/etudiant.service';
import { LogService } from '../../../general/log/log.service';
import { AuthService } from '../../../auth/services/auth.service';


@Component({
  selector: 'app-etudiant-devenir-prof',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './etudiant-devenir-prof.html',
  styleUrls: ['./etudiant-devenir-prof.css']
})
export class EtudiantDevenirProfComponent implements OnInit {
  monProfil: any = null;
  idUtilisateurTestLog: string = "8";
  listeMatieres: any[] = [];
  listeLangues: any[] = [];

  matieresSelectionnees: Set<string> = new Set();
  languesSelectionnees: Set<string> = new Set();

  // Le tableau qui va stocker les PDF
  fichiersCertif: File[] = [];

  constructor(
    private etudiantService: EtudiantService,
    private router: Router,
    private logService: LogService,
    private authService: AuthService
  ) {}

  ngOnInit(): void {
    this.monProfil = this.authService.getUtilisateurConnecte();
    this.etudiantService.getFiltresDisponibles().subscribe({
      next: (data) => {
        this.listeMatieres = data.matieres;
        this.listeLangues = data.langues;
      }
    });
  }

  toggleMatiere(nomMatiere: string): void {
    if (this.matieresSelectionnees.has(nomMatiere)) {
      this.matieresSelectionnees.delete(nomMatiere);
    } else {
      this.matieresSelectionnees.add(nomMatiere);
    }
  }

  toggleLangue(nomLangue: string): void {
    if (this.languesSelectionnees.has(nomLangue)) {
      this.languesSelectionnees.delete(nomLangue);
    } else {
      this.languesSelectionnees.add(nomLangue);
    }
  }

  // Méthode déclenchée quand l'utilisateur choisit des fichiers PDF
  onFichiersSelectionnes(event: any): void {
    if (event.target.files && event.target.files.length > 0) {
      // On convertit la liste de fichiers en tableau classique
      this.fichiersCertif = Array.from(event.target.files);
    }
  }

  soumettreFormulaire(): void {
    if (this.matieresSelectionnees.size === 0 || this.languesSelectionnees.size === 0) {
      alert("Erreur : Veuillez sélectionner au moins une matière ET une langue.");
      return;
    }

    if (this.fichiersCertif.length === 0) {
      alert("Erreur : Veuillez fournir au moins un certificat au format PDF.");
      return;
    }


    //Envoi du log "devnir prof" dans mongoDB
    this.logService.LogDevenirProf(
      this.idUtilisateurTestLog,
      Array.from(this.matieresSelectionnees),
      Array.from(this.languesSelectionnees),
      this.fichiersCertif
    );

    // On utilise FormData pour pouvoir envoyer des fichiers + du texte
    const formData = new FormData();
    formData.append('id_utilisateur', this.monProfil.id.toString());

    // On transforme nos tableaux en chaînes JSON pour les faire passer dans le FormData
    formData.append('matieres', JSON.stringify(Array.from(this.matieresSelectionnees)));
    formData.append('langues', JSON.stringify(Array.from(this.languesSelectionnees)));

    // On ajoute chaque PDF sélectionné au FormData
    this.fichiersCertif.forEach((fichier) => {
      formData.append('certificats[]', fichier, fichier.name);
    });



    // On envoie le FormData au services
    this.etudiantService.devenirProf(formData).subscribe({
      next: (reponse) => {
        if (reponse.succes) {
          alert(reponse.message);
          this.router.navigate(['/']);
        } else {
          alert(reponse.message);
        }
      },
      error: (err) => console.error("Erreur d'enregistrement", err)
    });
  }

}
