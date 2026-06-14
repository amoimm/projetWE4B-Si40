import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule } from '@angular/router';
import { EnseignantService } from '../../services/enseignant.service';
import { AuthService } from '../../../auth/services/auth.service';

@Component({
  selector: 'app-enseignant-accueil',
  standalone: true,
  imports: [CommonModule, RouterModule],
  templateUrl: './enseignant-accueil.component.html',
  styleUrls: ['./enseignant-accueil.component.css']
})
export class EnseignantAccueilComponent implements OnInit {
  stats: any = { nb_cours: 0, nb_eleves: 0 };
  nbNouveauMessages: number = 0;
  messages_new: any[] = [];
  rdvs: any[] = [];
  nbRdvEnAttente: number = 0;
  userName: string = 'test';

  constructor(
    private enseignantService: EnseignantService,
    private authService: AuthService
  ) {}

  ngOnInit(): void {
    const user = this.authService.getUtilisateurConnecte();
    if (!user || !user.id) return;

    this.enseignantService.getDashboard(user.id).subscribe({
      next: (data: any) => {
        this.stats = data.stats;
        this.nbNouveauMessages = data.messages?.nb_nouveau_messages || 0;
        this.messages_new = data.messages_new || [];
        this.rdvs = data.rdvs || [];
        this.nbRdvEnAttente = data.nb_rdv_en_attente?.nb_rdv_en_attente || 0;
        this.userName = data.user_nom || '';
      },
      error: (err) => {
        console.error('Erreur lors du chargement du dashboard', err);
      }
    });
  }
}
