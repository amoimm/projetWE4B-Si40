import { Component,OnInit } from '@angular/core';
import { RouterOutlet } from '@angular/router';
import { MainNavComponent } from '../../../general/component/main-nav/main-nav';
import { AuthService } from '../../../auth/services/auth.service';

@Component({
  selector: 'app-etudiant-layout',
  standalone: true,
  imports: [RouterOutlet, MainNavComponent],
  templateUrl: './etudiant-layout.html',
  styleUrls: ['./etudiant-layout.css']
})
export class EtudiantLayoutComponent implements OnInit {
  monProfil: any = null;
  constructor(private authService: AuthService) {}
  ngOnInit(): void {
    this.monProfil = this.authService.getUtilisateurConnecte();
  }
}
