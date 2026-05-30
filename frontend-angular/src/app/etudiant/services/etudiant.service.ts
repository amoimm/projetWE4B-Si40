import { Injectable } from '@angular/core';
import { HttpClient, HttpParams } from '@angular/common/http'; // <-- Ajout de HttpParams ici !
import { Observable } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class EtudiantService {
  // --- URLs d'API REST pointant vers le backend PHP ---
  private apiUrl = 'http://localhost/projetWE4B-SI40/backend-angular/etudiant/recuperer_etudiant.php';
  private updateUrl = 'http://localhost/projetWE4B-SI40/backend-angular/etudiant/modifier_etudiant.php';
  private apiCoursUrl = 'http://localhost/projetWE4B-SI40/backend-angular/etudiant/api-cours.php';

  constructor(private http: HttpClient) { }

  // ==========================================
  // SECTION PROFIL
  // ==========================================

  getProfilEtudiant(userId: number): Observable<any> {
    return this.http.get<any>(`${this.apiUrl}?user_id=${userId}`);
  }

  updateProfilEtudiant(userId: number, donneesMisesAJour: any): Observable<any> {
    return this.http.post<any>(this.updateUrl, { user_id: userId, ...donneesMisesAJour });
  }

  // ==========================================
  // SECTION RECHERCHE DE COURS
  // ==========================================

  rechercherCours(recherche: string, prixMax: number | null): Observable<any[]> {
    let params = new HttpParams();

    if (recherche) {
      params = params.set('recherche', recherche);
    }
    if (prixMax !== null) {
      params = params.set('prix_max', prixMax.toString());
    }

    return this.http.get<any[]>(this.apiCoursUrl, { params });
  }
}
