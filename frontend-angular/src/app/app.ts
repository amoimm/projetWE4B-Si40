import { RouterOutlet } from '@angular/router';
import { Component, OnInit } from '@angular/core';
import { LogService } from './general/log/log.service';

@Component({
  selector: 'app-root',
  standalone: true,
  imports: [RouterOutlet],
  templateUrl: './app.html',
  styleUrl: './app.css'
})

export class AppComponent implements OnInit {
  title = 'frontend-angular';
  userId: string = "9";
  constructor(private logService: LogService) {}
  ngOnInit() {
    // Ce log sera envoyé automatiquement dès que l'application Angular démarrera
    this.logService.LogConnexion(`L'utilisateur numéro ${this.userId} a démarré le site !`, "INFO",this.userId);
  }
}
