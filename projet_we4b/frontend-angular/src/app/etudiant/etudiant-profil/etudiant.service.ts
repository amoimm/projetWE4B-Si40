import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class EtudiantService {
  // On cible le script PHP qui utilise ton config.php
  private apiUrl = 'http://localhost/WE4B-SI40/projet_we4b/etudiant/recuperer_etudiant.php';
  constructor(private http: HttpClient) { }

  getProfilEtudiant(userId: number): Observable<any> {
    return this.http.get<any>(`${this.apiUrl}?user_id=${userId}`);
  }
}
