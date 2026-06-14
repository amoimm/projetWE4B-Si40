import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class EnseignantService {
  private baseUrl = 'http://localhost/projetWE4B-SI40/backend-angular/enseignant/api';

  constructor(private http: HttpClient) { }


  getDashboard(): Observable<any> {
    return this.http.get(`${this.baseUrl}/get_dashboard.php`);
  }

  getCours(userId: number, params?: any): Observable<any> {
    const queryParams = { ...params, user_id: userId };
    return this.http.get(`${this.baseUrl}/get_mes_cours.php`, { params: queryParams });
  }

  getMatieres(userId: number): Observable<any> {
    return this.http.get(`${this.baseUrl}/get_matieres.php?user_id=${userId}`);
  }

  getLangues(userId: number): Observable<any> {
    return this.http.get(`${this.baseUrl}/get_langues.php?user_id=${userId}`);
  }

  creerCours(data: any): Observable<any> {
    return this.http.post(`${this.baseUrl}/nouveau_cours.php`, data, {
      headers: { 'Content-Type': 'application/json' }
    });
  }

  supprimerCours(idCours: number): Observable<any> {
    return this.http.post(`${this.baseUrl}/delete_cours.php`, { id_cours: idCours }, {
      headers: { 'Content-Type': 'application/json' }
    });
  }

  getCoursDetails(idCours: number): Observable<any> {
    return this.http.get(`${this.baseUrl}/get_cours_details.php?id=${idCours}`);
  }

  modifierCours(data: any): Observable<any> {
    return this.http.post(`${this.baseUrl}/modifier_cours.php`, data, {
      headers: { 'Content-Type': 'application/json' }
    });
  }

  getConversations(): Observable<any[]> {
    return this.http.get<any[]>(`${this.baseUrl}/Conversations.php`);
  }

  getMessages(conversationId: number): Observable<any[]> {
    return this.http.get<any[]>(`${this.baseUrl}/charger_messages_enseignant.php?id=${conversationId}`);
  }

  envoyerMessage(data: any): Observable<any> {
    return this.http.post(`${this.baseUrl}/envoi_message_enseignant.php`, data, {
      headers: { 'Content-Type': 'application/json' }
    });
  }
}


