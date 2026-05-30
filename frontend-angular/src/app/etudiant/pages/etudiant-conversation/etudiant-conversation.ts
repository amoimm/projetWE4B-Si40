import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { ActivatedRoute } from '@angular/router';
import { EtudiantService } from '../../services/etudiant.service';

@Component({
  selector: 'app-etudiant-conversation',
  standalone: true,
  imports: [CommonModule, FormsModule],
  templateUrl: './etudiant-conversation.html',
  styleUrls: ['./etudiant-conversation.css']
})
export class EtudiantConversationComponent implements OnInit {
  idCours: number = 18;
  idEtudiantTest: number = 8;
  infoCours: any = null;
  messages: any[] = [];
  nouveauMessage: string = '';

  // --- NOUVEAU : Variables pour les RDV ---
  rdvs: any[] = [];
  languesProf: any[] = [];

  // Variables de la modale
  modaleOuverte: boolean = false;
  formRdv = {
    date_cours: '',
    heure_cours: '',
    duree_cours: '1h',
    lieu: '',
    langue_cours: ''
  };

  constructor(private route: ActivatedRoute, private etudiantService: EtudiantService) {}

  ngOnInit(): void {
    this.route.params.subscribe(params => {
      this.idCours = +params['id'];
      this.chargerDiscussion();
    });
  }

  chargerDiscussion(): void {
    this.etudiantService.getConversation(this.idCours, this.idEtudiantTest).subscribe({
      next: (data) => {
        this.infoCours = data.info_cours;
        this.messages = data.messages || [];
        this.rdvs = data.rdvs || [];
        this.languesProf = data.langues_prof || [];

        // Sélectionner la première langue par défaut si elle existe
        if (this.languesProf.length > 0) {
          this.formRdv.langue_cours = this.languesProf[0].nom;
        }
      },
      error: (err) => console.error('Erreur :', err)
    });
  }

  envoyerMessage(): void {
    if (!this.nouveauMessage.trim()) return;
    const contenuMsg = this.nouveauMessage;
    const idConv = this.infoCours?.id_conv || null;

    this.messages.push({
      id_redacteur: this.idEtudiantTest,
      contenu: contenuMsg,
      heure: new Date().toISOString()
    });
    this.nouveauMessage = '';

    this.etudiantService.envoyerMessage(this.idCours, idConv, this.idEtudiantTest, contenuMsg).subscribe({
      next: (response) => {
        if (!this.infoCours) this.infoCours = {};
        if (!this.infoCours.id_conv && response.id_conv) this.infoCours.id_conv = response.id_conv;
      }
    });
  }

  // --- NOUVEAU : Fonctions de la Modale ---
  ouvrirModale() { this.modaleOuverte = true; }
  fermerModale() { this.modaleOuverte = false; }

  soumettreRdv() {
    const payload = {
      id_cours: this.idCours,
      id_eleve: this.idEtudiantTest,
      ...this.formRdv
    };

    this.etudiantService.demanderRdv(payload).subscribe({
      next: () => {
        // Ajout visuel immédiat pour l'utilisateur
        this.rdvs.push({
          date_heure: `${this.formRdv.date_cours} ${this.formRdv.heure_cours}:00`,
          lieu: this.formRdv.lieu,
          est_valide: 0
        });
        this.fermerModale();
        alert('Demande envoyée au professeur !');
      },
      error: (err) => alert('Erreur lors de la demande')
    });
  }
}
