import { Component } from '@angular/core';
import { RouterOutlet } from '@angular/router';
import { MainNavComponent } from '../../../general/main-nav/main-nav';

@Component({
  selector: 'app-enseignant-layout',
  standalone: true,
  imports: [RouterOutlet, MainNavComponent],
  templateUrl: './enseignant-layout.html',
  styleUrl: './enseignant-layout.css',
})
export class EnseignantLayout {
  realUserRole: string = 'admin';
}