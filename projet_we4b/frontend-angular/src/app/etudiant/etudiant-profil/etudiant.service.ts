import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class EtudiantService {
  // Vos URLs d'API REST pointant vers le backend PHP
  private apiUrl = 'http://localhost/WE4B-SI40/projet_we4b/etudiant/recuperer_etudiant.php';
  private updateUrl = 'http://localhost/WE4B-SI40/projet_we4b/etudiant/modifier_etudiant.php';

  constructor(private http: HttpClient) { }

  // Requête GET pour charger le profil
  getProfilEtudiant(userId: number): Observable<any> {
    return this.http.get<any>(`${this.apiUrl}?user_id=${userId}`);
  }

  // Requête POST pour modifier le profil
  updateProfilEtudiant(userId: number, donneesMisesAJour: any): Observable<any> {
    return this.http.post<any>(this.updateUrl, { user_id: userId, ...donneesMisesAJour });
  }
}
