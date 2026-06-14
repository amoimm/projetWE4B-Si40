import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { EnseignantService } from '../../services/enseignant.service';
import { AuthService } from '../../../auth/services/auth.service';

@Component({
  selector: 'app-conversations',
  standalone: true,
  imports: [CommonModule, FormsModule],
  templateUrl: './conversations.component.html'
})
export class ConversationsComponent implements OnInit {
  monId: number = 1;
  conversations: any[] = [];
  messages: any[] = [];
  nouveauMessage: string = '';
  convActiveId: number | null = null;

  constructor(
    private service: EnseignantService,
    private authService: AuthService
  ) {}

  ngOnInit() {
    const user = this.authService.getUtilisateurConnecte();
    if (user && user.id) {
      this.monId = Number(user.id);
    }
    this.service.getConversations().subscribe(data => this.conversations = data);
  }

  selectionnerConv(id: number) {
    this.convActiveId = id;
    this.service.getMessages(id).subscribe(data => this.messages = data);
  }

  envoyer() {
    if (this.nouveauMessage.trim() && this.convActiveId) {
      this.service.envoyerMessage({
        id_conv: this.convActiveId,
        message: this.nouveauMessage
      }).subscribe(() => {
        this.nouveauMessage = '';
        this.selectionnerConv(this.convActiveId!);
      });
    }
  }
}
