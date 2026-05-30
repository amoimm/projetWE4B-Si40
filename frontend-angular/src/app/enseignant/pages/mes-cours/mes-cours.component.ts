import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { EnseignantService } from '../../services/enseignant.service';

@Component({
  selector: 'app-mes-cours',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './mes-cours.component.html'
})
export class MesCoursComponent implements OnInit {
  listeDesCours: any[] = [];

  constructor(private enseignantService: EnseignantService) {}

  ngOnInit(): void {
    // Appel du service au chargement de la page
    this.enseignantService.getCours().subscribe({
      next: (data) => {
        this.listeDesCours = data;
      },
      error: (err) => {
        console.error('Erreur lors du chargement des cours:', err);
      }
    });
  }
}