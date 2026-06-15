import { Component } from '@angular/core';
import { RouterOutlet } from '@angular/router';
import { MainNavComponent } from '../../../general/component/main-nav/main-nav';

@Component({
  selector: 'app-admin-layout',
  standalone: true,
  imports: [RouterOutlet, MainNavComponent],
  templateUrl: './admin-layout.html',
  styleUrls: ['./admin-layout.css']
})
export class AdminLayout {}
