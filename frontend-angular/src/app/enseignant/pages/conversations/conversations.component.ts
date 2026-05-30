import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { EnseignantService } from '../../services/enseignant.service';

@Component({
  selector: 'app-conversations',
  standalone: true,
  imports: [CommonModule, FormsModule],
  templateUrl: './conversations.component.html'
})
export class ConversationsComponent implements OnInit {
  conversations: any[] = [];
  messages: any[] = [];
  nouveauMessage: string = '';
  convActiveId: number | null = null;

  constructor(private service: EnseignantService) {}

  ngOnInit() {
    this.service.getConversations().subscribe(data => this.conversations = data);
  }

  selectionnerConv(id: number) {
    this.convActiveId = id;
    this.service.getMessages(id).subscribe(data => this.messages = data);
  }

  envoyer() {
    if (this.nouveauMessage.trim() && this.convActiveId) {
      this.service.envoyerMessage({
        id_destinataire: this.convActiveId, 
        message: this.nouveauMessage
      }).subscribe(() => {
        this.nouveauMessage = '';
        this.selectionnerConv(this.convActiveId!); // Rafraîchir
      });
    }
  }
}