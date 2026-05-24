import { Router } from '@angular/router';
import { CommonModule, NgClass, NgSwitch } from '@angular/common';
import { Component, OnInit } from '@angular/core';
import { AccountBalance, ConversionBTC, ConversionUSD, Transaction } from '../../models/saldo.model';
import { BankService } from '../../services/bank.service';

@Component({
  selector: 'app-home-page',
  imports: [NgSwitch, NgClass, CommonModule],
  templateUrl: './home-page.html',
  styleUrl: './home-page.css',
})

export class HomePage implements OnInit {


  data?: AccountBalance;
  movimenti: Transaction[] = [];
  conversionUSD?: ConversionUSD;
  conversionBTC?: ConversionBTC;
  inboundTotal: number = 0;
  outboundTotal: number = 0;

  constructor(private bankService: BankService, private router: Router) {}

  calculateTotals(): void {
    this.inboundTotal = this.movimenti
      .filter(m => m.type === 'deposit')
      .reduce((sum, current) => sum + Number(current.amount), 0);

    this.outboundTotal = this.movimenti
      .filter(m => m.type === 'withdrawal')
      .reduce((sum, current) => sum + Number(current.amount), 0);
  }

  ngOnInit(): void {

      this.bankService.getAccountBalance().subscribe({
        next: (response) => {
          this.data = response;
          this.calculateTotals();
        },
        error: (err) => {
          console.error("Error during call:", err);
        }
      });

      this.bankService.getTransactions().subscribe({
        next: (response: any) => {
          this.movimenti = [...response].reverse();
        },
        error: (err) => {
          console.error("Error during call:", err);
          if (err.status === 401) {
            console.warn("Sessione expired. Redirecting to login...");
            this.router.navigate(['/auth']);
          }
        }
      })



      this.bankService.getConvUSD().subscribe({
        next:(response: any) => {
          this.conversionUSD = response;
        },
        error: (err) => {
           console.error("Error during call:", err);
        }
      })

      this.bankService.getConvBTC().subscribe({
         next:(response: any) => {
          this.conversionBTC = response;
        },
        error: (err) => {
           console.error("Error during call:", err);
        }
      })
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
    opacity: isNear ? 0.4 : 0.15,
    blur: isNear ? '4px' : '0px',
    zIndex: isNear ? 20 : -1
  };
});
}
