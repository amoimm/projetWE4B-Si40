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
  private apiFiltresUrl = 'http://localhost/projetWE4B-Si40/backend-angular/etudiant/api-filtres.php';
  private apiDetailsConversationUrl = 'http://localhost/projetWE4B-Si40/backend-angular/etudiant/api-details-conversation.php';
  private apiConversationsUrl = 'http://localhost/projetWE4B-Si40/backend-angular/etudiant/api-conversations.php';
  private apiEnvoiMessageUrl = 'http://localhost/projetWE4B-Si40/backend-angular/etudiant/api-envoi-message.php';
  private apiDemandeRdvUrl = 'http://localhost/projetWE4B-Si40/backend-angular/etudiant/api-demande-rdv.php';

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

  getFiltresDisponibles(): Observable<any> {
    return this.http.get<any>(this.apiFiltresUrl);
  }

  // Requête GET pour filtrer les cours (on lui passe un objet contenant tous les filtres)
  rechercherCours(filtres: any): Observable<any[]> {
    let params = new HttpParams();

    // On ajoute chaque paramètre dynamiquement s'il est rempli
    if (filtres.recherche) params = params.set('recherche', filtres.recherche);
    if (filtres.prixMax !== null) params = params.set('prix_max', filtres.prixMax.toString());
    if (filtres.filtreMatiere) params = params.set('filtre_matiere', filtres.filtreMatiere);
    if (filtres.filtreLangue) params = params.set('filtre_langue', filtres.filtreLangue);
    if (filtres.filtreMode) params = params.set('filtre_mode', filtres.filtreMode);
    if (filtres.filtreSuivi) params = params.set('filtre_suivi', filtres.filtreSuivi);
    if (filtres.filtreAvis) params = params.set('filtre_avis', filtres.filtreAvis);

    return this.http.get<any[]>(this.apiCoursUrl, { params });
  }

  // ==========================================
  // SECTION CHAT & CONVERSATIONS
  // ==========================================

  getConversations(idEleve: number): Observable<any[]> {
    return this.http.get<any[]>(`${this.apiConversationsUrl}?id_eleve=${idEleve}`);
  }

  // Charger une conversation spécifique (infos du cours + messages)
  getConversation(idCours: number, idEleve: number): Observable<any> {
    return this.http.get<any>(`${this.apiDetailsConversationUrl}?id_cours=${idCours}&id_eleve=${idEleve}`);
  }

  // Envoi d'un message en BDD (POST)
  envoyerMessage(idCours: number, idConv: number | null, idRedacteur: number, contenu: string): Observable<any> {
    const payload = {
      id_cours: idCours,
      id_conv: idConv,
      id_redacteur: idRedacteur,
      contenu: contenu
    };
    return this.http.post<any>(this.apiEnvoiMessageUrl, payload);
  }
  demanderRdv(payload: any): Observable<any> {
    return this.http.post<any>(this.apiDemandeRdvUrl, payload);
  }
}
