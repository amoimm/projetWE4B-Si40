import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { EnseignantService } from '../../services/enseignant.service';
import {LogService} from '../../../general/log/log.service';

@Component({
  selector: 'app-nouveau-cours',
  standalone: true,
  imports: [CommonModule, FormsModule],
  templateUrl: './nouveau-cours.component.html'
})
export class NouveauCoursComponent implements OnInit {
  teacherId: string = '8'; // ID temporaire de l'enseignant connecté
  matieres: any[] = [];
  cours: any = { matiere: '', suivi: false, description: '' };

  constructor(
    private service: EnseignantService,
    private logService: LogService
  ) {}

  ngOnInit() {
    this.service.getMatieres().subscribe(data => this.matieres = data);
  }

  onSubmit() {
    console.log("Envoi du formulaire :", this.cours);
    // Ici, fais un this.http.post(...) pour envoyer les données à ton API

    this.logService.LogEvenement(
      'TEACHER_COURSE',
      'CREATE_COURSE',
      `Nouveau cours créé pour la matière ID: ${this.cours.matiere}`,
      'INFO',
      this.teacherId,
      {
        matiere: this.cours.matiere,
        suivi: this.cours.suivi,
        description: this.cours.description
      }
    );
  }
}
