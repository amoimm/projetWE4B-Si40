import { inject } from '@angular/core';
import { Router, CanActivateFn } from '@angular/router';
import { AuthService } from '../../auth/services/auth.service';

export const roleGuard = (roleRequis: string): CanActivateFn => {
  return () => {
    const authService = inject(AuthService);
    const router = inject(Router);

    const user = authService.getUtilisateurConnecte();

    if (user && user.role === roleRequis) {
      return true;
    }

    router.navigate(['/auth/connexion']);
    return false;
  };
};
