import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';

@Injectable({
  providedIn: 'root'
})
export class LogService {
  private apiUrl = 'http://localhost/projetWE4B-Si40/backend-angular/api-logs.php';

  constructor(private http: HttpClient) {}

  envoyerLog(message: string, level: string = 'INFO', id_user : string) {
    const payload = { level: level, message: message, id_user: id_user};

    this.http.post(this.apiUrl, payload).subscribe({
      next: (response) => console.log('Log stocké dans MongoDB !', response),
      error: (error) => console.error('Erreur lors de l\'envoi du log', error)
    });
  }
}
