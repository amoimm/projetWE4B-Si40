import { inject } from '@angular/core';
import { Router, CanActivateFn } from '@angular/router';
import { AuthService } from '../../auth/services/auth.service';

export const roleGuard = (rolesRequis: string[]): CanActivateFn => {
  return () => {
    const authService = inject(AuthService);
    const router = inject(Router);

    const user = authService.getUtilisateurConnecte();

    if (user && rolesRequis.includes(user.role)) {
      return true;
    }

    router.navigate(['/auth/connexion']);
    return false;
  };
};
