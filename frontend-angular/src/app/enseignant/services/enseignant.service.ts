import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class EnseignantService {
  private baseUrl = 'http://localhost/WE4B-SI40/projetWE4B-Si40/projet_we4b/api';

  constructor(private http: HttpClient) { }

  getCours(): Observable<any> {
    return this.http.get(`${this.baseUrl}/get_cours.php`);
  }
}