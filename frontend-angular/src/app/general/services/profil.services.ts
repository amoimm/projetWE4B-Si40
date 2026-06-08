import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class ProfilServices {
  private apiUrl = 'http://localhost/projetWE4B-SI40/backend-angular/general/recuperer_profil.php';
  private updateUrl = 'http://localhost/projetWE4B-SI40/backend-angular/general/modifier_profil.php';

  constructor(private http: HttpClient) { }

  getProfil(userId: number, role: string): Observable<any> {
    return this.http.get<any>(`${this.apiUrl}?user_id=${userId}&role=${role}`);
  }

  updateProfil(userId: number, role: string, donneesMisesAJour: any): Observable<any> {
    return this.http.post<any>(this.updateUrl, { user_id: userId, role: role, ...donneesMisesAJour });
  }
}
