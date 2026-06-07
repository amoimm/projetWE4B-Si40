import { Component } from '@angular/core';
import { RouterOutlet } from '@angular/router';
import { MainNavComponent } from '../../../general/main-nav/main-nav';

@Component({
  selector: 'app-etudiant-layout',
  standalone: true,
  imports: [RouterOutlet, MainNavComponent],
  templateUrl: './etudiant-layout.html',
  styleUrls: ['./etudiant-layout.css']
})
export class EtudiantLayoutComponent {
  // Plus tard, tu pourras injecter un service ici pour récupérer le vrai rôle de l'utilisateur connecté en BDD !
  realUserRole: string = 'admin';
}
