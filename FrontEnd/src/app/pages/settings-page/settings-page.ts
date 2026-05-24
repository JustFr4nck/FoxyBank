import { AccountData } from './../../models/account.models';
import { BankService } from './../../services/bank.service';
import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { AccountBalance } from '../../models/saldo.model';
import { RouterLink, RouterLinkActive} from '@angular/router';

@Component({
  selector: 'app-settings-page',
  imports: [CommonModule, RouterLink, RouterLinkActive],
  templateUrl: './settings-page.html',
  styleUrl: './settings-page.css',
})
export class SettingsPage implements OnInit {

  accountData? : AccountData;
  balance? : AccountBalance;
  constructor(private bankService: BankService){}

  ngOnInit(): void {

     this.bankService.getAccountUser().subscribe({
      next: (response) => {
        this.accountData = response;
      },
      error: (err) => console.error(err),
    });

    this.bankService.getAccountBalance().subscribe({
      next: (response) => {
        this.balance = response;
      },
      error: (err) => console.error(err),
    });
  }
}
