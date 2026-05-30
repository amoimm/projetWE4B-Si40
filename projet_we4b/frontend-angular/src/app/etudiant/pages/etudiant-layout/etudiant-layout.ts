import { Component } from '@angular/core';
import { RouterOutlet } from '@angular/router';
import { EtudiantNavComponent } from '../../components/etudiant-nav/etudiant-nav'; // Ajuste le chemin si besoin

@Component({
  selector: 'app-etudiant-layout',
  standalone: true,
  imports: [RouterOutlet, EtudiantNavComponent],
  templateUrl: './etudiant-layout.html',
  styleUrls: ['./etudiant-layout.css']
})
export class EtudiantLayoutComponent { }
