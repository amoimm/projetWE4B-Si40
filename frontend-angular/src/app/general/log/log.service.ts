import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';

@Injectable({
  providedIn: 'root'
})
export class LogService {
  private apiUrl = 'http://localhost/projetWE4B-Si40/backend-angular/api-logs.php';

  constructor(private http: HttpClient) {}

  LogConnexion(
    message: string,
    level: string = 'INFO',
    id_user : number
  ) {
    const payload = { level: level, message: message, id_user: id_user};

    this.http.post(this.apiUrl, payload).subscribe({
      next: (response) => console.log('Log stocké dans MongoDB !', response),
      error: (error) => console.error('Erreur lors de l\'envoi du log', error)
    });
  }

  LogDevenirProf(
    id_user: string,
    matieres: string[],
    langues: string[],
    fichiersCertif: File[]
  ) {
    const formData = new FormData();

    // Métadonnées pour le routage/log côté PHP
    formData.append('type_log', 'CANDIDATURE_PROF');
    formData.append('id_user', id_user);

    // Message par défaut ou construit dynamiquement pour le champ 'message' du $document
    const messageLog = `Soumission du profil enseignant (${matieres.join(', ')})`;
    formData.append('message', messageLog);

    // Ajout des matières et langues (on les sérialise en JSON pour le transport FormData)
    formData.append('matieres', JSON.stringify(matieres));
    formData.append('langues', JSON.stringify(langues));

    // Ajout des fichiers de certifications (gestion du multi-fichiers si nécessaire)
    fichiersCertif.forEach((fichier, index) => {
      formData.append(`certifications[${index}]`, fichier);
    });

    // Envoi de la requête HTTP
    this.http.post(this.apiUrl, formData).subscribe({
      next: (response) => console.log('Candidature prof enregistrée avec succès !', response),
      error: (error) => console.error('Erreur lors de l\'envoi de la candidature', error)
    });
  }

  LogEvenement(
    category: string,
    action: string,
    message: string,
    level: string = 'INFO',
    id_user: string,
    details: any = {}
  ) {
    const payload = {
      level: level,
      category: category,
      action: action,
      message: message,
      id_user: id_user,
      details: details
    };
    this.http.post(this.apiUrl, payload).subscribe({
      next: (response) => console.log(`Log ${category}/${action} enregistré !`, response),
      error: (error) => console.error('Erreur lors de l\'envoi du log', error)
    });
  }

}
