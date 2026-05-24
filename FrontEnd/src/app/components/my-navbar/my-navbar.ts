import { BankService } from './../../services/bank.service';
import { Component, OnInit, HostListener } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterLink, RouterLinkActive, Router } from '@angular/router';
import { routes } from '../../app.routes';
import { AccountData } from '../../models/account.models';

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
  accountData? : AccountData;

  constructor(
    private router: Router,
    private bankService: BankService,
  ) {}

  ngOnInit(): void {
    this.navLinks = routes
      .filter(
        (route) =>
          route.path !== '' &&
          route.path !== undefined &&
          route.path !== 'login' &&
          route.path !== '**' &&
          !route.path.includes(':'),
      )
      .map((route) => ({
        path: `/${route.path}`,
        label: route.path!,
      }));

    this.bankService.getAccountUser().subscribe({
      next: (response) => {
        this.accountData = response;
      },
      error: (err) => console.error(err),
    });
  }

  onLogout(): void {
    this.bankService.logout().subscribe({
      next: (response: string) => {
        console.log('Risposta backend:', response);
        this.router.navigate(['/login']);
      },
      error: (err) => {
        console.error('Error during logout: ', err);
        this.router.navigate(['/login']);
      },
    });
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
}
