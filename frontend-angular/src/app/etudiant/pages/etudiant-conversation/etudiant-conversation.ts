import { Component, OnInit, OnDestroy, ViewChild, ElementRef } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { ActivatedRoute } from '@angular/router';
import { EtudiantService } from '../../services/etudiant.service';
import { interval, Subscription } from 'rxjs';
import { AuthService } from '../../../auth/services/auth.service';

@Component({
  selector: 'app-etudiant-conversation',
  standalone: true,
  imports: [CommonModule, FormsModule],
  templateUrl: './etudiant-conversation.html',
  styleUrls: ['./etudiant-conversation.css']
})
export class EtudiantConversationComponent implements OnInit, OnDestroy {
  @ViewChild('scrollMe') private myScrollContainer!: ElementRef;

  idCours: number = 18;
  monProfil: any = null;

  infoCours: any = null;
  messages: any[] = [];
  nouveauMessage: string = '';

  rdvs: any[] = [];
  languesProf: any[] = [];
  dateMin: string = '';

  private actualisationAuto!: Subscription;

  modaleOuverte: boolean = false;
  formRdv = {
    date_cours: '',
    heure_cours: '',
    duree_cours: '1h',
    lieu: '',
    langue_cours: ''
  };

  constructor(
    private authService: AuthService,
    private route: ActivatedRoute,
    private etudiantService: EtudiantService
  ) {}

  ngOnInit(): void {
    this.monProfil = this.authService.getUtilisateurConnecte();

    this.route.params.subscribe(params => {
      this.idCours = +params['id'];

      this.chargerDiscussion();

      this.actualisationAuto = interval(2000).subscribe(() => {
        this.chargerDiscussion();
      });
      const aujourdhui = new Date();
      this.dateMin = aujourdhui.toISOString().split('T')[0];
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
    if (!this.monProfil) return;

    this.etudiantService.getConversation(this.idCours, this.monProfil.id).subscribe({
      next: (data) => {
        const ancienNombreMessages = this.messages.length;
        this.infoCours = data.info_cours;
        this.messages = data.messages || [];
        this.rdvs = data.rdvs || [];
        this.languesProf = data.langues_prof || [];

        if (this.languesProf.length > 0 && !this.formRdv.langue_cours) {
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
      id_redacteur: this.monProfil.id,
      contenu: contenuMsg,
      heure: new Date().toISOString()
    });
    this.nouveauMessage = '';
    this.scrollToBottom();

    // Envoi à la BDD
    this.etudiantService.envoyerMessage(this.idCours, idConv, this.monProfil.id, contenuMsg).subscribe({
      next: (response) => {
        if (!this.infoCours) this.infoCours = {};
        if (!this.infoCours.id_conv && response.id_conv) this.infoCours.id_conv = response.id_conv;
      }
    });
  }

  // --- Fonctions de la Modale ---
  ouvrirModale() { console.log("Clic reçu !");this.modaleOuverte = true; }
  fermerModale() { this.modaleOuverte = false; }

  soumettreRdv() {
    //verif
    if (
      !this.formRdv.date_cours ||
      !this.formRdv.heure_cours ||
      !this.formRdv.duree_cours ||
      !this.formRdv.lieu ||
      !this.formRdv.langue_cours
    ) {
      alert('Veuillez remplir tous les champs du formulaire.');
      return;
    }

    const dateChoisie = new Date(this.formRdv.date_cours);
    const aujourdhui = new Date();
    aujourdhui.setHours(0, 0, 0, 0);

    if (dateChoisie.getTime() < aujourdhui.getTime()) {
        alert('Vous ne pouvez pas choisir une date passée.');
      return;
    }

    const payload = {
      id_cours: this.idCours,
      id_eleve: this.monProfil.id,
      ...this.formRdv
    };

    this.etudiantService.demanderRdv(payload).subscribe({
      next: () => {
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

    this.etudiantService.annulerRdv(idRdv, this.monProfil.id).subscribe({
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
