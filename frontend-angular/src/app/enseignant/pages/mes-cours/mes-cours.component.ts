import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { RouterModule } from '@angular/router';

@Component({
  selector: 'app-mes-cours',
  standalone: true,
  imports: [CommonModule, FormsModule, RouterModule],
  templateUrl: './mes-cours.component.html',
  styleUrls: ['./mes-cours.component.css']
})
export class MesCoursComponent implements OnInit {
  coursList: any[] = [];

  recherche: string = '';
  filtreMatiere: string = '';
  filtreLangue: string = '';
  filtreAvis: string = '';

  ngOnInit() {
    this.chargerCours();
  }

  chargerCours() {
    // Ici, tu feras ton appel API (ex: this.coursService.getMesCours().subscribe(...))
  }

  supprimerCours(id: number) {
    if (confirm('Voulez-vous vraiment supprimer ce cours ?')) {
      // Appel au services pour supprimer
    }
  }
}
