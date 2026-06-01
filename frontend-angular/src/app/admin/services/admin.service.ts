import { Injectable } from '@angular/core';
import { HttpClient, HttpParams } from '@angular/common/http';
import { Observable } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class AdminService {
  // URLs des endpoints API du backend PHP
  private apiBaseUrl = 'http://localhost/projetWE4B-SI40/backend-angular/admin';

  private statsUrl = `${this.apiBaseUrl}/api-stats.php`;
  private utilisateursUrl = `${this.apiBaseUrl}/api-utilisateurs.php`;
  private coursUrl = `${this.apiBaseUrl}/api-cours.php`;
  private matieresUrl = `${this.apiBaseUrl}/api-matieres.php`;
  private languesUrl = `${this.apiBaseUrl}/api-langues.php`;

  constructor(private http: HttpClient) { }

  // ==========================================
  // SECTION DASHBOARD / STATS
  // ==========================================

  // Récupère les statistiques du tableau de bord (nombre users, cours, messages)
  getStats(): Observable<any> {
    return this.http.get<any>(this.statsUrl);
  }

  // Récupère les utilisateurs récents (les 5 derniers)
  getUtilisateursRecents(): Observable<any[]> {
    return this.http.get<any[]>(`${this.utilisateursUrl}?action=recents`);
  }

  // ==========================================
  // SECTION UTILISATEURS
  // ==========================================

  // Récupère la liste des utilisateurs avec filtres et pagination
  getUtilisateurs(search: string = '', rang: string = '', page: number = 1): Observable<any> {
    let params = new HttpParams();
    if (search) params = params.set('search', search);
    if (rang !== '') params = params.set('rang', rang);
    params = params.set('page', page.toString());

    return this.http.get<any>(this.utilisateursUrl, { params });
  }

  // Modifie le rôle (rang) d'un utilisateur
  modifierRangUtilisateur(idUtilisateur: number, nouveauRang: number): Observable<any> {
    return this.http.post<any>(`${this.utilisateursUrl}?action=modifier_rang`, {
      id_utilisateur: idUtilisateur,
      rang: nouveauRang
    });
  }

  // Supprime un utilisateur
  supprimerUtilisateur(idUtilisateur: number): Observable<any> {
    return this.http.post<any>(`${this.utilisateursUrl}?action=supprimer`, {
      id_utilisateur: idUtilisateur
    });
  }

  // ==========================================
  // SECTION COURS
  // ==========================================

  // Récupère la liste des cours avec possibilité de recherche
  getCours(search: string = ''): Observable<any[]> {
    let params = new HttpParams();
    if (search) params = params.set('search', search);

    return this.http.get<any[]>(this.coursUrl, { params });
  }

  // Supprime un cours
  supprimerCours(idCours: number): Observable<any> {
    return this.http.post<any>(`${this.coursUrl}?action=supprimer`, {
      id_cours: idCours
    });
  }

  // ==========================================
  // SECTION CONFIGURATION (Matières & Langues)
  // ==========================================

  // Récupère toutes les matières
  getMatieres(): Observable<any[]> {
    return this.http.get<any[]>(this.matieresUrl);
  }

  // Ajoute une nouvelle matière
  ajouterMatiere(nomMatiere: string): Observable<any> {
    return this.http.post<any>(this.matieresUrl, {
      action: 'add',
      nom_matiere: nomMatiere
    });
  }

  // Récupère toutes les langues
  getLangues(): Observable<any[]> {
    return this.http.get<any[]>(this.languesUrl);
  }

  // Ajoute une nouvelle langue
  ajouterLangue(nomLangue: string): Observable<any> {
    return this.http.post<any>(this.languesUrl, {
      action: 'add',
      nom_langue: nomLangue
    });
  }
}
