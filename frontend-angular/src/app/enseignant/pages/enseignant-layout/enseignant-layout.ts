import { Component,OnInit } from '@angular/core';
import { RouterOutlet } from '@angular/router';
import { MainNavComponent } from '../../../general/component/main-nav/main-nav';
import { AuthService } from '../../../auth/services/auth.service';

@Component({
  selector: 'app-enseignant-layout',
  standalone: true,
  imports: [RouterOutlet, MainNavComponent],
  templateUrl: './enseignant-layout.html',
  styleUrl: './enseignant-layout.css',
})
export class EnseignantLayout {
  monProfil: any = null;
  constructor(private authService: AuthService) {}
  ngOnInit(): void {
    this.monProfil = this.authService.getUtilisateurConnecte();
  }
}
