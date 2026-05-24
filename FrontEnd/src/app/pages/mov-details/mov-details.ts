import { BankService } from './../../services/bank.service';
import { Component, inject, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ActivatedRoute, Router, RouterLink } from '@angular/router';
import { FormsModule } from '@angular/forms';
import { Transaction } from '../../models/saldo.model';

@Component({
  selector: 'app-mov-details',
  standalone: true,
  imports: [CommonModule, FormsModule, RouterLink],
  templateUrl: './mov-details.html',
  styleUrl: './mov-details.css',
})
export class MovDetails implements OnInit {
  private route = inject(ActivatedRoute);
  id: number | null = null;
  movimento?: Transaction;

  isEditing = false;
  mockDesc = '';
  movimentoId = '';
  currentYear = '';

  constructor(
    private bankService: BankService,
    private router: Router,
  ) {}

  ngOnInit(): void {
    this.id = +this.route.snapshot.paramMap.get('id')!;

    this.bankService.getTransactionDet(this.id).subscribe({
      next: (response) => {
        this.movimento = response;
      },
      error: (err) => {
        console.error('Error during call:', err);
      },
    });
  }

  saveDescription(): void {
    if (this.id && this.mockDesc.trim()) {
      this.bankService.updateDescTrans(this.id, this.mockDesc).subscribe({
        next: (updatedMovement: Transaction) => {
          if (this.movimento) {
            this.movimento.description = updatedMovement.description || this.mockDesc;
          }
          this.isEditing = false;
        },
        error: (err) => {
          console.error('Error during modify:', err);
        },
      });
    } else {
      console.error('BAD REQUEST');
    }
  }

  toggleEdit(): void {
    if (this.isEditing) {
      this.saveDescription();
    } else {
      this.mockDesc = this.movimento?.description || '';
      this.isEditing = true;
    }
  }

  deleteMov(): void {
    if (this.id) {
      this.bankService.deleteTrans(this.id).subscribe({
        next: () => {
          this.router.navigate(['/listaMovimenti']);
        },
        error: (err) => {
          console.error('Error during delete:', err);
        },
      });
    } else {
      console.warn("BAD REQUEST");
    }
  }

  //animation background
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
      zIndex: isNear ? 20 : -1,
    };
  });
}
