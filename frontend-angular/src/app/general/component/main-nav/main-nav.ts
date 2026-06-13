  import { Component, Input } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterLink, RouterLinkActive,Router } from '@angular/router';
import { AuthService } from '../../../auth/services/auth.service';


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
    private router: Router
  ) {}
  deconnexion(): void {
    this.authService.deconnexion();
    this.router.navigate(['/auth/connexion']);
  }
}
