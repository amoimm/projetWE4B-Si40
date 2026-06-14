import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class EnseignantService {
  private baseUrl = 'http://localhost/projetWE4B-SI40/backend-angular/enseignant/api';

  constructor(private http: HttpClient) { }


  getDashboard(): Observable<any> { return this.http.get(`${this.baseUrl}/get_dashboard.php`); }
  getCours(): Observable<any> { return this.http.get(`${this.baseUrl}/get_mes_cours.php`); }
  getMatieres(): Observable<any> { return this.http.get(`${this.baseUrl}/get_matieres.php`); }
  getProfil(): Observable<any> { return this.http.get(`${this.baseUrl}/get_profil.php`); }
  getConversations() { return this.http.get<any[]>(`${this.baseUrl}/Conversations.php`); }
  getMessages(conversationId: number) { return this.http.get<any[]>(`${this.baseUrl}/charger_messages_enseignant.php?id=${conversationId}`); }
  envoyerMessage(data: {id_destinataire: number, message: string}) { return this.http.post(`${this.baseUrl}/envoi_message_enseignant.php`, data); }
}

