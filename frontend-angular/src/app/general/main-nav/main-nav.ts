import { Component, Input } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterLink, RouterLinkActive } from '@angular/router';

@Component({
  selector: 'app-main-nav',
  standalone: true,
  imports: [CommonModule, RouterLink, RouterLinkActive],
  templateUrl: './main-nav.html',
  styleUrls: ['./main-nav.css']
})
export class MainNavComponent {
  @Input() userRole: string = 'etudiant';
  @Input() globalRole: string = 'etudiant';
}
