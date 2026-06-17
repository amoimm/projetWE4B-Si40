  import { Component, Input } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterLink, RouterLinkActive,Router } from '@angular/router';
import { AuthService } from '../../../auth/services/auth.service';
  import {LogService} from '../../log/log.service';


@Component({
  selector: 'app-main-nav',
  standalone: true,
  imports: [CommonModule, RouterLink, RouterLinkActive],
  templateUrl: './main-nav.html',
  styleUrls: ['./main-nav.css']
})
export class MainNavComponent {
  @Input() userRole: string = '';
  @Input() globalRole: string = '';

  constructor(
    private authService: AuthService,
    private router: Router,
    private logService: LogService
  ) {}
  deconnexion(): void {
    const user = this.authService.getUtilisateurConnecte();
    if (user && user.id) {
      // Enregistrement du log de déconnexion dans MongoDB
      this.logService.LogEvenement(
        'AUTHENTICATION',
        'LOGOUT',
        `L'utilisateur numéro ${user.id} s'est déconnecté`,
        'INFO',
        user.id.toString()
      );
    }
    this.authService.deconnexion();
    this.router.navigate(['/auth/connexion']);
  }
}
