import { Component, OnInit, HostListener } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterLink, RouterLinkActive, Router } from '@angular/router';
import { routes } from '../../app.routes';


@Component({
  selector: 'app-my-navbar',
  standalone: true,
  imports: [CommonModule, RouterLink, RouterLinkActive],
  templateUrl: './my-navbar.html',
  styleUrls: ['./my-navbar.css'],
})
export class MyNavbar implements OnInit {
  navLinks: any[] = [];
  isMenuOpen = false;
  isAccountMenuOpen = false;

  constructor(private router: Router){};

  ngOnInit(): void {
    this.navLinks = routes
      .filter(
        (route) =>
          route.path !== '' &&
          route.path !== undefined &&
          route.path !== 'auth' &&
          route.path !== '**' &&
          !route.path.includes(':'),
      )
      .map((route) => ({
        path: `/${route.path}`,
        label: route.path!,
      }));
  }

  @HostListener('window:resize', ['$event'])
  onResize(event: Event) {
    if (window.innerWidth > 1024 && this.isMenuOpen) {
      this.toggleMenu();
    }
  }

  @HostListener('document:click', ['$event'])
  clickout(event: any) {
    if (!event.target.closest('.account-container')) {
      this.isAccountMenuOpen = false;
    }
  }

  toggleMenu() {
    this.isMenuOpen = !this.isMenuOpen;
    const overflow = this.isMenuOpen ? 'hidden' : 'auto';
    document.documentElement.style.overflow = overflow;
    document.body.style.overflow = overflow;
  }

  toggleAccountMenu(event: Event) {
    event.stopPropagation();
    this.isAccountMenuOpen = !this.isAccountMenuOpen;
  }

  getCrazyLabel(label: string): string {
    const mapping: { [key: string]: string } = {
      listaMovimenti: 'TRANSACTIONS_LIST',
      movDetails: 'TRANSACTION_DETAIL',
      prelievo: 'WITHDRAW',
      deposito: 'DEPOSIT',
    };
    return mapping[label] || label.toUpperCase();
  }

  logout() {
    this.router.navigate(['/auth']);
    console.log('Session terminated.');
  }
}
