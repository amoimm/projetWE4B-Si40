# Rapport technique : Architecture NoSQL et Modélisation MongoDB

Ce rapport détaille les choix d'architecture, la structure des collections, les modèles de documents et les requêtes appliquées à la base de données NoSQL (MongoDB) du projet **CoursConnect**.

---

## 1. Concepts fondamentaux : Embedding vs Linking

Avant d'aborder la structure spécifique de notre base de données, il convient de définir et de justifier les deux stratégies de modélisation dans MongoDB.

### A. L'Embedding (Documents imbriqués)
L'**Embedding** consiste à stocker des données associées directement au sein d'un seul et unique document parent sous forme de sous-documents ou de tableaux d'objets.

*   **Avantages** :
    *   **Performance en lecture** : Récupération de toutes les données associées en une seule requête d'I/O (pas de jointures coûteuses).
    *   **Opérations atomiques** : Possibilité de mettre à jour le document et ses sous-documents de manière atomique en une seule écriture.
*   **Inconvénients** :
    *   **Limite de taille** : La taille maximale d'un document dans MongoDB est limitée à **16 Mo**. Les tableaux de sous-documents qui croissent indéfiniment (ex. les commentaires sur un blog populaire) risquent de saturer cette limite.
    *   **Duplication des données** : Si une information imbriquée doit être modifiée globalement, il faut mettre à jour tous les documents parents où elle apparaît, ce qui peut générer des incohérences.

### B. Le Linking (Références)
Le **Linking** consiste à normaliser les données en créant des collections distinctes et en liant les documents entre eux par des identifiants uniques (références comme `_id` ou des clés étrangères).

*   **Avantages** :
    *   **Pas de duplication** : L'information n'est stockée qu'à un seul endroit, facilitant les mises à jour et assurant la cohérence des données.
    *   **Évite la limite des 16 Mo** : Les documents restent de petite taille puisque les listes volumineuses sont déportées dans d'autres collections.
*   **Inconvénients** :
    *   **Performance diminuée en lecture** : Nécessite plusieurs requêtes réseau ou l'utilisation de l'opérateur `$lookup` (équivalent NoSQL d'une jointure SQL), ce qui ralentit les temps de réponse.

---

## 2. Choix d'Architecture : Système Hybride SQL/NoSQL

Dans notre projet, nous avons fait le choix d'une **architecture hybride** :

1.  **MySQL (`projetwe4a-si40`)** : Gère les données structurées et transactionnelles nécessitant une intégrité relationnelle forte (utilisateurs, cours, matières de référence, langues de référence, messages).
2.  **MongoDB (`coursconnect_nosql`)** : Gère les données semi-structurées, volumineuses ou à écriture fréquente (logs d'activité, candidatures d'enseignants).

### Justification de la liaison (Linking) SQL $\leftrightarrow$ NoSQL
Dans nos documents MongoDB, nous utilisons un **Linking** vers la base MySQL à travers le champ `id_user` (ou `deleted_user_id`, `target_user_id`).
*   **Pourquoi ne pas imbriquer l'utilisateur dans MongoDB ?**
    Si nous imbriquions le profil complet de l'utilisateur (nom, prénom, email, mot de passe) dans chaque log d'activité ou candidature, toute modification du profil de l'utilisateur (ex. changement d'email ou de mot de passe dans MySQL) nécessiterait une mise à jour lourde et asynchrone de centaines de documents MongoDB. Le **Linking** par ID permet de garder des documents MongoDB légers et de maintenir une seule source de vérité pour le profil utilisateur dans MySQL.

---

## 3. Structure des Collections MongoDB

La base de données NoSQL contient deux collections principales :

### A. Collection `activity_logs`
Cette collection enregistre l'ensemble des actions utilisateurs, des connexions et des opérations d'administration. Elle utilise un modèle **hybride** (Linking pour l'utilisateur, Embedding pour les détails).

#### Schéma logique du document
*   `_id` : `ObjectId` (Généré automatiquement par MongoDB, contient le timestamp de création).
*   `level` : `String` (Gravité du log : `INFO`, `WARNING`, `ERROR`).
*   `category` : `String` (Catégorie fonctionnelle : `AUTHENTICATION`, `STUDENT_SEARCH`, `TEACHER_COURSE`, `ADMIN`).
*   `action` : `String` (Action précise : `LOGIN`, `LOGOUT`, `APPLY_FILTERS`, `CREATE_COURSE`, `UPDATE_COURSE`, `ADD_LANGUE`, `ADD_MATIERE`, `CHANGE_USER_ROLE`, `DELETE_USER`).
*   `message` : `String` (Description textuelle humaine).
*   `id_user` : `Integer|String|Null` (**Linking** vers l'utilisateur MySQL).
*   `timestamp` : `String` (Format `d-m-Y H:i:s` pour un affichage rapide).
*   `details` : `Document` (**Embedded** - structure dynamique variant selon l'action).

### B. Collection `demande_prof`
Cette collection gère les dossiers de candidature des étudiants souhaitant devenir enseignants. Elle s'appuie fortement sur l'**Embedding** pour conserver l'état du dossier à la soumission.

#### Schéma logique du document
*   `_id` : `ObjectId`.
*   `level` : `String` (Généralement `INFO`).
*   `message` : `String`.
*   `id_user` : `Integer|String` (**Linking** vers le candidat MySQL).
*   `timestamp` : `String`.
*   `context` : `Document` (**Embedded**) :
    *   `matieres` : `Array of Strings` (Liste des matières demandées, ex: `["Maths", "Physique"]`).
    *   `langues` : `Array of Strings` (Liste des langues parlées, ex: `["Français", "Anglais"]`).
    *   `uploaded_files_names` : `Array of Strings` (Noms des pièces justificatives et diplômes téléversés).
    *   `status` : `String` (`attente_validation`, `valide`, `rejete`).

---

## 4. Exemples de Documents JSON Réels

Voici des exemples concrets de documents tels qu'ils sont stockés dans MongoDB :

### Document 1 : Recherche d'un élève (avec filtres imbriqués - Embedding)
*Ici, les critères de recherche sont imbriqués (`details`) car ils forment un bloc figé décrivant l'action à un instant T.*
```json
{
  "_id": { "$oid": "66743b12a8f9c2d1b4a8e901" },
  "level": "INFO",
  "category": "STUDENT_SEARCH",
  "action": "APPLY_FILTERS",
  "message": "L'élève a filtré les cours",
  "id_user": 14,
  "timestamp": "20-06-2026 14:32:15",
  "details": {
    "recherche": "algèbre",
    "matiere": "2",
    "langue": "1",
    "prix_max": 35,
    "mode": "visio"
  }
}
```

### Document 2 : Candidature pour devenir Professeur (Embedding & Linking)
*Les matières et les fichiers de certification sont imbriqués (Embedded Array) car ils font partie intégrante du dossier. L'utilisateur est lié par son `id_user` (Linking).*
```json
{
  "_id": { "$oid": "66743c5fa8f9c2d1b4a8e905" },
  "level": "INFO",
  "message": "Devenir Prof: Soumission du profil enseignant.",
  "id_user": 25,
  "timestamp": "20-06-2026 14:45:02",
  "context": {
    "matieres": ["Mathématiques", "Physique-Chimie"],
    "langues": ["Français", "Anglais"],
    "uploaded_files_names": ["diplome_licence_maths.pdf", "cv_enseignant.pdf"],
    "status": "attente_validation"
  }
}
```

### Document 3 : Modification d'un rôle utilisateur par l'admin (Log d'activité)
```json
{
  "_id": { "$oid": "66743e02a8f9c2d1b4a8e910" },
  "level": "INFO",
  "category": "ADMIN",
  "action": "CHANGE_USER_ROLE",
  "message": "L'administrateur a changé le rôle de l'utilisateur Jean Dupont (Email: jean.dupont@email.com) de Étudiant à Professeur",
  "id_user": 1,
  "timestamp": "20-06-2026 14:52:18",
  "details": {
    "target_user_id": 25,
    "target_user_email": "jean.dupont@email.com",
    "ancien_rang": 0,
    "nouveau_rang": 1
  }
}
```

---

## 5. Requêtes MongoDB Utilisées (PHP Driver)

Le projet utilise des pipelines d'agrégation MongoDB évolués pour extraire des indicateurs statistiques temps réel destinés au tableau de bord d'administration.

### Requête 1 : Top 5 des matières les plus recherchées
Cette requête filtre les logs de recherche, regroupe par matière imbriquée, compte le nombre de recherches, trie par ordre décroissant et limite au top 5.

```php
$pipeline_matieres = [
    // 1. Filtrer uniquement les recherches d'élèves contenant une matière valide
    ['$match' => [
        'category' => 'STUDENT_SEARCH',
        'details.matiere' => ['$ne' => '']
    ]],
    // 2. Grouper par l'ID de la matière stocké dans le document imbriqué details
    ['$group' => [
        '_id' => '$details.matiere',
        'count' => ['$sum' => 1]
    ]],
    // 3. Trier par nombre de recherches décroissant
    ['$sort' => ['count' => -1]],
    // 4. Limiter aux 5 premiers résultats
    ['$limit' => 5]
];

$cursor = $activitylogsCollection->aggregate($pipeline_matieres);
```

### Requête 2 : Calcul du nombre d'utilisateurs connectés (Activité des 30 dernières minutes)
Cette requête calcule les utilisateurs actifs en se basant sur la date d'émission de l'ObjectId et en excluant ceux qui ont émis une action `LOGOUT`.

```php
// Détermination de la limite de temps (il y a 30 minutes)
$thirtyMinutesAgo = new DateTime('now', new DateTimeZone('Europe/Paris'));
$thirtyMinutesAgo->modify('-30 minutes');
$hexTime = dechex($thirtyMinutesAgo->getTimestamp()) . str_repeat('0', 16);
$objectIdMin = new MongoDB\BSON\ObjectId($hexTime);

$pipeline_connected = [
    // 1. Filtrer les documents créés depuis 30 minutes ayant un id_user valide
    ['$match' => [
        '_id' => ['$gte' => $objectIdMin],
        'id_user' => ['$exists' => true, '$ne' => null, '$nin' => [0, '0', '']]
    ]],
    // 2. Trier du plus récent au plus ancien
    ['$sort' => ['_id' => -1]],
    // 3. Grouper par utilisateur pour ne garder que sa dernière action
    ['$group' => [
        '_id' => '$id_user',
        'latest_action' => ['$first' => '$action']
    ]],
    // 4. Exclure les utilisateurs dont la dernière action est LOGOUT
    ['$match' => [
        'latest_action' => ['$ne' => 'LOGOUT']
    ]],
    // 5. Compter les documents restants
    ['$count' => 'count']
];

$cursor_connected = $activitylogsCollection->aggregate($pipeline_connected)->toArray();
$user_connected = isset($cursor_connected[0]['count']) ? (int)$cursor_connected[0]['count'] : 0;
```

### Requête 3 : Activité des 7 derniers jours (Volume de logs par jour)
Cette requête extrait la date textuelle du timestamp, regroupe les logs par jour, puis trie chronologiquement.

```php
$pipeline_activite = [
    // 1. Extraire les 10 premiers caractères du timestamp (jj-mm-aaaa)
    ['$project' => [
        'day' => ['$substr' => ['$timestamp', 0, 10]]
    ]],
    // 2. Grouper par jour et compter
    ['$group' => [
        '_id' => '$day',
        'count' => ['$sum' => 1]
    ]],
    // 3. Trier par date décroissante
    ['$sort' => ['_id' => -1]],
    // 4. Limiter aux 7 derniers jours
    ['$limit' => 7]
];

$cursor_activite = $activitylogsCollection->aggregate($pipeline_activite);
```

---

## 6. Synthèse des Choix de Conception

| Collection | Concept Utilisé | Raison du Choix |
| :--- | :--- | :--- |
| `activity_logs.details` | **Embedding** | Les détails d'un log sont statiques (figés au moment de l'événement). L'intégration directe permet des requêtes rapides sans jointure. |
| `activity_logs.id_user` | **Linking** | L'utilisateur est stocké dans MySQL. Un simple ID lie le log à l'utilisateur sans dupliquer ses informations changeantes. |
| `demande_prof.context` | **Embedding** | Les listes de matières, langues et justificatifs sont de taille finie et font corps avec la candidature. Facilite l'affichage complet du dossier. |
| `demande_prof.id_user` | **Linking** | Permet de relier la candidature au profil utilisateur existant sans redondance. |
