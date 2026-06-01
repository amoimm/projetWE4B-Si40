import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule } from '@angular/router';
import { EnseignantService } from '../../services/enseignant.service';

@Component({
  selector: 'app-profil-enseignant',
  standalone: true,
  imports: [CommonModule, RouterModule],
  templateUrl: './profil-enseignant.component.html'
})
export class ProfilEnseignantComponent implements OnInit {
  user: any = {};

  constructor(private service: EnseignantService) {}

  ngOnInit() {
    this.service.getProfil().subscribe(data => this.user = data);
  }
}