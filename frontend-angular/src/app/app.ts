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
  title = 'Cours Connect';
  userId: number = 0;
  constructor(
    private logService: LogService,
  ) {}
  ngOnInit() {
    // Ce log sera envoyé automatiquement dès que l'application Angular démarrera
    this.logService.LogConnexion(`Un utilisateur est arrivé sur le site !`, "INFO",this.userId);
  }
}
