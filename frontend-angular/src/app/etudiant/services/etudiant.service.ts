import { Injectable } from '@angular/core';
import { HttpClient, HttpParams } from '@angular/common/http';
import { Observable } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class EtudiantService {
  private apiUrl = 'http://localhost/projetWE4B-Si40/backend-angular/etudiant/recuperer_etudiant.php';
  private updateUrl = 'http://localhost/projetWE4B-Si40/backend-angular/etudiant/modifier_etudiant.php';
  private apiCoursUrl = 'http://localhost/projetWE4B-Si40/backend-angular/etudiant/api-cours.php';
  private apiFiltresUrl = 'http://localhost/projetWE4B-Si40/backend-angular/etudiant/api-filtres.php';
  private apiDetailsConversationUrl = 'http://localhost/projetWE4B-Si40/backend-angular/etudiant/api-details-conversation.php';
  private apiConversationsUrl = 'http://localhost/projetWE4B-Si40/backend-angular/etudiant/api-conversations.php';
  private apiEnvoiMessageUrl = 'http://localhost/projetWE4B-Si40/backend-angular/etudiant/api-envoi-message.php';
  private apiDemandeRdvUrl = 'http://localhost/projetWE4B-Si40/backend-angular/etudiant/api-demande-rdv.php';
  private apiSupprimerRdvUrl = 'http://localhost/projetWE4B-Si40/backend-angular/etudiant/api-supprimer-rdv.php';
  private apiDevenirProfUrl = 'http://localhost/projetWE4B-Si40/backend-angular/etudiant/api-devenir-prof.php';

  constructor(private http: HttpClient) { }

  getProfilEtudiant(userId: number): Observable<any> {
    return this.http.get<any>(`${this.apiUrl}?user_id=${userId}`);
  }

  updateProfilEtudiant(userId: number, donneesMisesAJour: any): Observable<any> {
    return this.http.post<any>(this.updateUrl, { user_id: userId, ...donneesMisesAJour });
  }

  getFiltresDisponibles(): Observable<any> {
    return this.http.get<any>(this.apiFiltresUrl);
  }

  rechercherCours(filtres: any): Observable<any[]> {
    let params = new HttpParams();

    if (filtres.recherche) params = params.set('recherche', filtres.recherche);
    if (filtres.prixMax !== null) params = params.set('prix_max', filtres.prixMax.toString());
    if (filtres.filtreMatiere) params = params.set('filtre_matiere', filtres.filtreMatiere);
    if (filtres.filtreLangue) params = params.set('filtre_langue', filtres.filtreLangue);
    if (filtres.filtreMode) params = params.set('filtre_mode', filtres.filtreMode);
    if (filtres.filtreSuivi) params = params.set('filtre_suivi', filtres.filtreSuivi);
    if (filtres.filtreAvis) params = params.set('filtre_avis', filtres.filtreAvis);

    return this.http.get<any[]>(this.apiCoursUrl, { params });
  }


  getConversations(idEleve: number): Observable<any[]> {
    return this.http.get<any[]>(`${this.apiConversationsUrl}?id_eleve=${idEleve}`);
  }

  getConversation(idCours: number, idEleve: number): Observable<any> {
    return this.http.get<any>(`${this.apiDetailsConversationUrl}?id_cours=${idCours}&id_eleve=${idEleve}`);
  }

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

  annulerRdv(idRdv: number, idEleve: number): Observable<any> {
    return this.http.post<any>(this.apiSupprimerRdvUrl, { id_rdv: idRdv, id_eleve: idEleve });
  }

  devenirProf(formData: FormData): Observable<any> {
    return this.http.post<any>(this.apiDevenirProfUrl, formData);
  }
}
