import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders, HttpParams } from '@angular/common/http';
import { Observable } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class EnseignantService {
  private baseUrl = 'http://localhost/projetWE4B-Si40/backend-angular/enseignant/api';

  constructor(private http: HttpClient) { }

  private getHeaders(userId: number): HttpHeaders {
    return new HttpHeaders({
      'Content-Type': 'application/json',
      'X-User-Id': userId.toString()
    });
  }

  getDashboard(userId: number): Observable<any> {
    return this.http.get(`${this.baseUrl}/get_dashboard.php`, { headers: this.getHeaders(userId) });
  }

  getCours(userId: number, params?: any): Observable<any> {
    return this.http.get(`${this.baseUrl}/get_mes_cours.php`, {
      headers: this.getHeaders(userId),
      params: params
    });
  }

  getMatieres(userId: number): Observable<any> {
    return this.http.get(`${this.baseUrl}/get_matieres.php`, { headers: this.getHeaders(userId) });
  }

  getLangues(userId: number): Observable<any> {
    return this.http.get(`${this.baseUrl}/get_langues.php`, { headers: this.getHeaders(userId) });
  }

  creerCours(userId: number, data: any): Observable<any> {
    return this.http.post(`${this.baseUrl}/nouveau_cours.php`, data, {
      headers: this.getHeaders(userId)
    });
  }

  supprimerCours(userId: number, idCours: number): Observable<any> {
    return this.http.post(`${this.baseUrl}/delete_cours.php`, { id_cours: idCours }, {
      headers: this.getHeaders(userId)
    });
  }

  getCoursDetails(userId: number, idCours: number): Observable<any> {
    const params = new HttpParams().set('id', idCours.toString());
    return this.http.get(`${this.baseUrl}/get_cours_details.php`, {
      headers: this.getHeaders(userId),
      params: params
    });
  }

  modifierCours(userId: number, data: any): Observable<any> {
    return this.http.post(`${this.baseUrl}/modifier_cours.php`, data, {
      headers: this.getHeaders(userId)
    });
  }

  getConversations(userId: number): Observable<any[]> {
    return this.http.get<any[]>(`${this.baseUrl}/Conversations.php`, { headers: this.getHeaders(userId) });
  }

  getMessages(userId: number, conversationId: number): Observable<any[]> {
    const params = new HttpParams().set('id', conversationId.toString());
    return this.http.get<any[]>(`${this.baseUrl}/charger_messages_enseignant.php`, {
      headers: this.getHeaders(userId),
      params: params
    });
  }

  envoyerMessage(userId: number, data: any): Observable<any> {
    return this.http.post(`${this.baseUrl}/envoi_message_enseignant.php`, data, {
      headers: this.getHeaders(userId)
    });
  }
}
