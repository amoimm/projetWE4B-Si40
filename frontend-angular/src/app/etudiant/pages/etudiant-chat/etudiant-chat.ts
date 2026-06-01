import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterLink } from '@angular/router';
import { EtudiantService } from '../../services/etudiant.service';

@Component({
  selector: 'app-etudiant-chat',
  standalone: true,
  imports: [CommonModule, RouterLink],
  templateUrl: './etudiant-chat.html',
  styleUrls: ['./etudiant-chat.css']
})
export class EtudiantChatComponent implements OnInit {
  conversations: any[] = [];
  idEtudiantTest = 8;

  constructor(private etudiantService: EtudiantService) {}

  ngOnInit(): void {
    this.etudiantService.getConversations(this.idEtudiantTest).subscribe({
      next: (data) => this.conversations = data,
      error: (err) => console.error('Erreur chargement conversations:', err)
    });
  }
}
