import { Transaction } from './../../models/saldo.model';
import { BankService } from './../../services/bank.service';
import { CommonModule } from '@angular/common';
import { Component, OnInit } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { Router } from '@angular/router';
import { RouterLink } from '@angular/router';

@Component({
  selector: 'app-prelievo',
  standalone: true,
  imports: [CommonModule, FormsModule, RouterLink],
  templateUrl: './prelievo.html',
  styleUrl: './prelievo.css',
})
export class Prelievo implements OnInit {
  importo: number = 0;
  particles: any[] = [];
  descrizione: string = '';


  saldoDisponibile: number = 0;
  tagliRapidi: number[] = [20, 50, 100, 200];

  icons = ['💸', '📉', '💨', '🚨', '🥀', '🔻'];

  constructor(
    private bankService: BankService,
    private router: Router,
  ) {}

   ngOnInit(): void {

      this.bankService.getAccountBalance().subscribe({
        next: (response) => {
          this.saldoDisponibile = response.balance;
        },
        error: (err) => {
          console.error("Error during call:", err);
        }
      });
   }

  selezionaImportoRapido(valore: number) {
    this.importo = valore;
  }

  faiPrelievo() {
    if (this.importo <= 0) {
      alert('Insert a valid import');
      return;
    }


    if (this.importo > this.saldoDisponibile) {
      alert('Insufficient funds available');
      return;
    }

    const payload = {
      amount: Number(this.importo),
      description: this.descrizione || 'No description',
    };


    this.bankService.doWithdrawals(payload).subscribe({
      next: (res) => {
        for (let i = 0; i < 20; i++) {
          this.createParticle();
        }

        setTimeout(() => {
          this.particles = [];
          this.router.navigate(['/listaMovimenti']);
        }, 2000);
      },
      error: (err) => console.error('Errore:', err),
    });
  }

  private createParticle() {
    const velocity = 80 + Math.random() * 150;
    const angle = Math.random() * Math.PI * 2;

    this.particles.push({
      id: Math.random(),
      icon: this.icons[Math.floor(Math.random() * this.icons.length)],
      x: Math.cos(angle) * velocity,
      y: Math.sin(angle) * velocity,
      rotate: Math.random() * 720,
    });
  }

  moneyRain = Array.from({ length: 40 }, (_, i) => {
    const isNear = Math.random() > 0.7;
    return {
      id: i,
      char: Math.random() > 0.5 ? '€' : '$',
      left: Math.floor(Math.random() * 100),
      delay: Math.random() * 10,
      duration: isNear ? 4 + Math.random() * 2 : 8 + Math.random() * 4,
      size: isNear ? 40 + Math.random() * 20 : 15 + Math.random() * 10,
      opacity: isNear ? 0.3 : 0.1,
      blur: isNear ? '4px' : '0px',
      zIndex: isNear ? 20 : -1,
    };
  });
}
