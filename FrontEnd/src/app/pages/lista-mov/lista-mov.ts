import { Component, OnInit, ViewChild } from '@angular/core'; // <-- Aggiungi ViewChild qui
import { CommonModule, NgClass } from '@angular/common';
import { RouterLink, Router } from '@angular/router';
import { BankService } from '../../services/bank.service';
import { Transaction } from '../../models/saldo.model';
import { BaseChartDirective } from 'ng2-charts';
import { ChartConfiguration, ChartType } from 'chart.js';

@Component({
  selector: 'app-lista-mov',
  imports: [NgClass, CommonModule, RouterLink, BaseChartDirective],
  templateUrl: './lista-mov.html',
  styleUrl: './lista-mov.css',
})
export class ListaMov implements OnInit {
  @ViewChild(BaseChartDirective) chart?: BaseChartDirective;

  movimenti: Transaction[] = [];
  public lineChartType: ChartType = 'line';

  public lineChartData: ChartConfiguration['data'] = {
    labels: [],
    datasets: [
      {
        data: [],
        label: 'Importo Movimento (€)',
        borderColor: '#7c3aed',
        backgroundColor: 'rgba(124, 58, 237, 0.1)',
        pointBackgroundColor: '#f97316',
        pointBorderColor: '#fff',
        pointHoverBackgroundColor: '#fff',
        pointHoverBorderColor: '#7c3aed',
        fill: 'origin',
        tension: 0.4
      }
    ]
  };

  public lineChartOptions: ChartConfiguration['options'] = {
    responsive: true,
    maintainAspectRatio: false,
    scales: {
      x: {
        grid: { color: 'rgba(255, 255, 255, 0.05)' },
        ticks: { color: 'rgba(255, 255, 255, 0.6)', font: { family: 'monospace', size: 10 } }
      },
      y: {
        grid: { color: 'rgba(255, 255, 255, 0.05)' },
        ticks: { color: 'rgba(255, 255, 255, 0.6)', font: { family: 'monospace', size: 10 } }
      }
    },
    plugins: {
      legend: {
        labels: { color: '#fff', font: { family: 'monospace', size: 11 } }
      }
    }
  };

  constructor(
    private bankService: BankService,
    private router: Router,
  ) {}

  ngOnInit(): void {
    this.bankService.getTransactions(1).subscribe({
      next: (response) => {
        this.movimenti = response;
        this.updateChartData();
      },
      error: (err) => {
        console.error('Error during call:', err);
      },
    });
  }

  private updateChartData(): void {
    if (!this.movimenti || this.movimenti.length === 0) return;

    const sortedMov = [...this.movimenti].reverse();

    this.lineChartData.labels = sortedMov.map(m => {
      const date = new Date(m.created_at);
      return date.toLocaleDateString('it-IT', { day: '2-digit', month: '2-digit' });
    });

    this.lineChartData.datasets[0].data = sortedMov.map(m =>
      m.type === 'withdrawal' ? -Number(m.amount) : Number(m.amount)
    );

    if (this.chart) {
      this.chart.update();
    }
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
      zIndex: isNear ? 20 : -1,
    };
  });
}
