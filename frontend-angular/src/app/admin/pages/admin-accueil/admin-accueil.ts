import { Component, OnInit, ChangeDetectorRef, Inject, PLATFORM_ID } from '@angular/core';
import { CommonModule, isPlatformBrowser } from '@angular/common';
import { AdminService } from '../../services/admin.service';
// Import de Chart.js
import { Chart, registerables } from 'chart.js';
Chart.register(...registerables);

@Component({
  selector: 'app-admin-accueil',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './admin-accueil.html',
  styleUrl: './admin-accueil.css'
})
export class AdminAccueil implements OnInit {
  // Variables pour stocker les données
  stats = {
    users: 0,
    cours: 0,
    messages: 0
  };

  utilisateursRecents: any[] = [];
  loading = true;
  error: string | null = null;

  constructor(
    private adminService: AdminService,
    private cdr: ChangeDetectorRef,
    @Inject(PLATFORM_ID) private platformId: Object
  ) {}

  ngOnInit() {
    this.chargerDonnees();
  }

  // Charge les stats et les utilisateurs récents
  chargerDonnees() {
    this.loading = true;
    this.error = null;

    // Requête 1 : Récupérer les stats (contient aussi les stats MongoDB pour les diagrammes)
    this.adminService.getStats().subscribe({
      next: (data) => {
        this.stats = data;
        console.log('Stats MySQL et MongoDB chargées :', data);
        
        // Requête 2 : Récupérer les utilisateurs récents
        this.adminService.getUtilisateursRecents().subscribe({
          next: (users) => {
            this.utilisateursRecents = users;
            this.loading = false;
            
            // Forcer Angular à rafraîchir le DOM pour que les <canvas> soient créés
            this.cdr.detectChanges();
            
            // Exécuter uniquement dans le navigateur
            if (isPlatformBrowser(this.platformId)) {
              setTimeout(() => {
                console.log('Initialisation des graphiques...');
                this.creerGraphiqueMatieres(data.top_matieres || []);
                this.creerGraphiqueActivite(data.activite_jours || []);
              });
            }
          },
          error: (err) => {
            console.error('Erreur utilisateurs:', err);
            this.error = 'Erreur lors du chargement des utilisateurs';
            this.loading = false;
          }
        });
      },
      error: (err) => {
        console.error('Erreur stats:', err);
        this.error = 'Erreur lors du chargement des statistiques';
        this.loading = false;
      }
    });
  }

  creerGraphiqueMatieres(matieresData: any[]) {
    const ctx = document.getElementById('matiereChart') as HTMLCanvasElement;
    if (!ctx) {
      console.error('Élément canvas "matiereChart" introuvable dans le DOM');
      return;
    }

    const labels = matieresData.map(item => item.matiere);
    const counts = matieresData.map(item => item.count);

    console.log('Données matière envoyées au graphique :', { labels, counts });

    if (labels.length === 0) {
      labels.push('Aucune donnée de recherche');
      counts.push(0);
    }

    // Palette de couleurs par matière
    const colorMap: { [key: string]: string } = {
      'mathématiques': '#3498db', // Bleu
      'maths': '#3498db',
      'physique-chimie': '#e74c3c', // Rouge
      'physique': '#e74c3c',
      'anglais': '#f1c40f', // Jaune
      'espagnol': '#9b59b6', // Violet
      'français': '#1abc9c', // Turquoise
      'informatique': '#2ecc71', // Vert
      'info': '#2ecc71',
      'histoire-géographie': '#e67e22', // Orange
      'histoire': '#e67e22',
      'svt': '#27ae60' // Vert sapin
    };

    const backgroundColors = labels.map(label => {
      const key = label.toLowerCase().trim();
      return colorMap[key] || '#34495e'; // Couleur sombre par défaut
    });

    new Chart(ctx, {
      type: 'bar',
      data: {
        labels: labels,
        datasets: [{
          label: 'Nombre de recherches',
          data: counts,
          backgroundColor: backgroundColors,
          borderRadius: 6
        }]
      },
      options: {
        responsive: true,
        plugins: {
          legend: { display: false }
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              stepSize: 1
            }
          }
        }
      }
    });
  }

  creerGraphiqueActivite(activiteData: any[]) {
    const ctx = document.getElementById('activiteChart') as HTMLCanvasElement;
    if (!ctx) {
      console.error('Élément canvas "activiteChart" introuvable dans le DOM');
      return;
    }

    const labels = activiteData.map(item => item.date);
    const counts = activiteData.map(item => item.count);

    console.log('Données activité envoyées au graphique :', { labels, counts });

    if (labels.length === 0) {
      labels.push('Aujourd\'hui');
      counts.push(0);
    }

    new Chart(ctx, {
      type: 'line',
      data: {
        labels: labels,
        datasets: [{
          label: 'Actions sur le site',
          data: counts,
          borderColor: '#2ecc71',
          tension: 0.3,
          fill: true,
          backgroundColor: 'rgba(46, 204, 113, 0.1)'
        }]
      },
      options: {
        responsive: true,
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              stepSize: 1
            }
          }
        }
      }
    });
  }

  // Retourne le badge de rôle approprié
  getBadgeRang(rang: number): { classe: string; texte: string } {
    switch(rang) {
      case 0: return { classe: 'badge-student', texte: 'Étudiant' };
      case 1: return { classe: 'badge-prof', texte: 'Professeur' };
      case 2: return { classe: 'badge-admin', texte: 'Admin' };
      default: return { classe: 'badge-other', texte: 'Inconnu' };
    }
  }
}
