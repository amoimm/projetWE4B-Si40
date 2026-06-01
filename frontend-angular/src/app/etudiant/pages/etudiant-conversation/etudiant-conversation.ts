import { Component, OnInit, OnDestroy,ViewChild, ElementRef } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { ActivatedRoute } from '@angular/router';
import { EtudiantService } from '../../services/etudiant.service';
import { interval, Subscription } from 'rxjs';

@Component({
  selector: 'app-etudiant-conversation',
  standalone: true,
  imports: [CommonModule, FormsModule],
  templateUrl: './etudiant-conversation.html',
  styleUrls: ['./etudiant-conversation.css']
})
export class EtudiantConversationComponent implements OnInit {
  @ViewChild('scrollMe') private myScrollContainer!: ElementRef;

  idCours: number = 18;
  idEtudiantTest: number = 8;
  infoCours: any = null;
  messages: any[] = [];
  nouveauMessage: string = '';

  rdvs: any[] = [];
  languesProf: any[] = [];

  private actualisationAuto!: Subscription;

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

      this.actualisationAuto = interval(2000).subscribe(() => {
        this.chargerDiscussion();
      });
    });
  }

  ngOnDestroy(): void {
    if (this.actualisationAuto) {
      this.actualisationAuto.unsubscribe();
    }
  }

  scrollToBottom(): void {
    setTimeout(() => {
      try {
        this.myScrollContainer.nativeElement.scrollTop = this.myScrollContainer.nativeElement.scrollHeight;
      } catch(err) { }
    }, 50);
  }
  chargerDiscussion(): void {
    this.etudiantService.getConversation(this.idCours, this.idEtudiantTest).subscribe({
      next: (data) => {
        const ancienNombreMessages = this.messages.length;
        this.infoCours = data.info_cours;
        this.messages = data.messages || [];
        this.rdvs = data.rdvs || [];
        this.languesProf = data.langues_prof || [];

        if (this.languesProf.length > 0) {
          this.formRdv.langue_cours = this.languesProf[0].nom;
        }
        if (this.messages.length > ancienNombreMessages) {
          this.scrollToBottom();
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
    this.scrollToBottom();
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
        this.chargerDiscussion();
      },
      error: (err) => alert('Erreur lors de la demande')
    });
  }
  annulerRdv(idRdv: number): void {
    if (!confirm('Êtes-vous sûr de vouloir annuler ce rendez-vous ?')) {
      return;
    }
    this.rdvs = this.rdvs.filter(rdv => rdv.id_rdv !== idRdv);
    this.etudiantService.annulerRdv(idRdv, this.idEtudiantTest).subscribe({
      next: () => {
        console.log('RDV annulé en base de données.');
        this.chargerDiscussion();
      },
      error: (err) => {
        alert('Erreur lors de l\'annulation du RDV.');
        this.chargerDiscussion();
      }
    });
  }
}
