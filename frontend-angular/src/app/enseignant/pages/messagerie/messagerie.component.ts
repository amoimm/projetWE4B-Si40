import { Component, OnInit, ViewChild, ElementRef, AfterViewChecked, OnDestroy } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { RouterModule, ActivatedRoute } from '@angular/router';
import { EnseignantService } from '../../services/enseignant.service';
import { AuthService } from '../../../auth/services/auth.service';

@Component({
  selector: 'app-messagerie',
  standalone: true,
  imports: [CommonModule, FormsModule, RouterModule],
  templateUrl: './messagerie.component.html',
  styleUrl: './messagerie.component.css',
})
export class MessagerieComponent implements OnInit, AfterViewChecked, OnDestroy {
  @ViewChild('chatBox') private chatBox!: ElementRef;

  monId: number = 0;
  idConv: number | null = null;
  idCours: number = 0;
  idEleve: number = 0;
  nouveauMessage: string = '';

  convInfo: any = null;
  messages: any[] = [];
  rendezVous: any[] = [];

  private refreshInterval: any;

  constructor(
    private route: ActivatedRoute,
    private service: EnseignantService,
    private auth: AuthService
  ) {}

  ngOnInit() {
    this.monId = Number(this.auth.getUtilisateurConnecte().id);

    this.route.queryParams.subscribe(params => {
      this.idConv = params['id_conv'] ? Number(params['id_conv']) : null;
      this.idCours = Number(params['id_cours']);
      this.idEleve = Number(params['id_eleve']);

      this.chargerDonnees();
    });

    this.refreshInterval = setInterval(() => {
      this.chargerDonnees();
    }, 2000);
  }

  ngAfterViewChecked() {
    this.scrollToBottom();
  }

  ngOnDestroy() {
    if (this.refreshInterval) {
      clearInterval(this.refreshInterval);
    }
  }

  private scrollToBottom(): void {
    if (this.chatBox) {
      this.chatBox.nativeElement.scrollTop = this.chatBox.nativeElement.scrollHeight;
    }
  }

  chargerDonnees() {
    if (this.idCours > 0 && this.idEleve > 0) {
      this.service.getRdv(this.idCours, this.idEleve, this.monId).subscribe(data => {
        this.rendezVous = data;
      });
    }

    if (this.idConv) {
      this.service.getMessages(this.monId, this.idConv).subscribe(data => {
        this.messages = data;
      });
    } else {
      this.messages = [];
    }
  }

  envoyerMessage() {
    if (!this.nouveauMessage.trim()) return;

    const payload = {
      id_conv: this.idConv || 0,
      id_eleve: this.idEleve,
      id_cours: this.idCours,
      message: this.nouveauMessage
    };

    this.service.envoyerMessage(this.monId, payload).subscribe((res: any) => {
      this.nouveauMessage = '';
      if (res.id_conv) {
        this.idConv = res.id_conv;
      }
      this.chargerDonnees();
    });
  }

  gererRdv(idRdv: number, action: string) {
    this.service.gererRdv(idRdv, action, this.monId).subscribe(() => {
      this.chargerDonnees();
    });
  }
}
