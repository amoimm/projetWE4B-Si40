import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common'; // Import requis pour le *ngIf
import { EtudiantService } from './etudiant.service';

@Component({
  selector: 'app-etudiant-profil',
  standalone: true,
  imports: [CommonModule], // Ajout du module commun
  templateUrl: './etudiant-profil.html',
  styleUrls: ['./etudiant-profil.css']
})
export class EtudiantProfilComponent implements OnInit {
  donneesEtudiant: any = null;

  constructor(private etudiantService: EtudiantService) { }

  ngOnInit(): void {
    // On ajoute explicitement le type ': any' pour rendre le compilateur heureux
    this.etudiantService.getProfilEtudiant(1).subscribe({
      next: (data: any) => {
        this.donneesEtudiant = data;
        console.log('Thème chargé avec succès :', this.donneesEtudiant);
      },
      error: (err: any) => {
        console.error('Erreur de liaison REST avec PHP :', err);
      }
    });
  }
}