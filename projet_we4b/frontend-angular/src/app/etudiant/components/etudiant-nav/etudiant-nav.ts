import { Component, OnInit, Input } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterLink, RouterLinkActive } from '@angular/router';

@Component({
  selector: 'app-etudiant-nav',
  standalone: true,
  imports: [CommonModule, RouterLink, RouterLinkActive],
  templateUrl: './etudiant-nav.html',
  styleUrls: ['./etudiant-nav.css']
})
export class EtudiantNavComponent implements OnInit {
  // Permet de recevoir le rôle (ex: 'etudiant', 'admin', 'enseignant')
  @Input() userRole: string = 'etudiant';

  constructor() { }

  ngOnInit(): void { }
}
