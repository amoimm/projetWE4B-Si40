import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule } from '@angular/router';
import { EnseignantService } from '../../services/enseignant.service';

@Component({
  selector: 'app-enseignant-accueil',
  standalone: true,
  imports: [CommonModule, RouterModule],
  templateUrl: './enseignant-accueil.component.html'
})
export class EnseignantAccueilComponent implements OnInit {
  stats: any = { nb_cours: 0, nb_eleves: 0 };
  messages: any = { nb_nouveau_messages: 0 };
  messages_new: any[] = [];
  rdvs: any[] = [];
  nb_rdv_en_attente: number = 0;

  constructor(private enseignantService: EnseignantService) {}

  ngOnInit(): void {
    this.enseignantService.getDashboard().subscribe((data: any) => {
      this.stats = data.stats;
      this.messages = data.messages;
      this.messages_new = data.messages_new;
      this.rdvs = data.rdvs;
      this.nb_rdv_en_attente = data.nb_rdv_en_attente;
    });
  }
}