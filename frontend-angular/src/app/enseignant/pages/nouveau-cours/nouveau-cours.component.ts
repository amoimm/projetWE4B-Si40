import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { EnseignantService } from '../../services/enseignant.service';

@Component({
  selector: 'app-nouveau-cours',
  standalone: true,
  imports: [CommonModule, FormsModule],
  templateUrl: './nouveau-cours.component.html'
})
export class NouveauCoursComponent implements OnInit {
  matieres: any[] = [];
  cours: any = { matiere: '', suivi: false, description: '' };

  constructor(private service: EnseignantService) {}

  ngOnInit() {
    this.service.getMatieres().subscribe(data => this.matieres = data);
  }

  onSubmit() {
    console.log("Envoi du formulaire :", this.cours);
    // Ici, fais un this.http.post(...) pour envoyer les données à ton API
  }
}