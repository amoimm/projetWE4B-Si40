import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class AuthService {
  // L'URL vers ton fichier PHP créé juste avant
  private apiConnexionUrl = 'http://localhost/projetWE4B-Si40/backend-angular/connect/api-connexion.php';
  private apiInscriptionUrl = 'http://localhost/TON_VRAI_CHEMIN/backend-angular/connect/api-inscription.php';


  constructor(private http: HttpClient) { }

  connexion(email: string, password: string): Observable<any> {
    return this.http.post<any>(this.apiConnexionUrl, { email, password });
  }

  inscription(donneesUtilisateur: any): Observable<any> {
    return this.http.post<any>(this.apiInscriptionUrl, donneesUtilisateur);
  }

  sauvegarderSession(utilisateur: any): void {
    localStorage.setItem('utilisateurConnecte', JSON.stringify(utilisateur));
  }

  getUtilisateurConnecte(): any {
    const data = localStorage.getItem('utilisateurConnecte');
    return data ? JSON.parse(data) : null;
  }

  deconnexion(): void {
    localStorage.removeItem('utilisateurConnecte');
  }
}
