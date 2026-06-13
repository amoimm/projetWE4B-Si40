import { RouterOutlet } from '@angular/router';
import { Component, OnInit } from '@angular/core';
import { LogService } from './general/log/log.service';
import { AuthService} from './auth/services/auth.service';

@Component({
  selector: 'app-root',
  standalone: true,
  imports: [RouterOutlet],
  templateUrl: './app.html',
  styleUrl: './app.css'
})

export class AppComponent implements OnInit {
  title = 'frontend-angular';
  userId: number = 0;
  constructor(
    private logService: LogService,
    private authService: AuthService
  ) {}
  ngOnInit() {
    // Ce log sera envoyé automatiquement dès que l'application Angular démarrera
    const user = this.authService.getUtilisateurConnecte()
    if(user){
      this.userId = user.id
    }else{
      console.warn("Aucun utilisateur n'est connecté");
    }
    this.logService.LogConnexion(`L'utilisateur numéro ${this.userId} a démarré le site !`, "INFO",this.userId);
  }
}
