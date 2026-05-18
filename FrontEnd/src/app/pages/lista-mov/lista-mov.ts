import { Component, OnInit, ViewChild } from '@angular/core';
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
  filtroAttivo: 'all' | 'deposit' | 'withdrawal' = 'all';
  public lineChartType: ChartType = 'line';

  public lineChartData: ChartConfiguration['data'] = {
    labels: [],
    datasets: [
      {
        data: [],
        label: 'Saldo Progressivo (€)',
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
        this.movimenti = response.sort((a, b) => new Date(b.created_at).getTime() - new Date(a.created_at).getTime());
        this.updateChartData();
      },
      error: (err) => {
        console.error('Error during call:', err);
      },
    });
  }

  get movimentiFiltrati(): Transaction[] {
    if (this.filtroAttivo === 'all') {
      return this.movimenti;
    }
    return this.movimenti.filter(m => m.type === this.filtroAttivo);
  }

  setFiltro(filtro: 'all' | 'deposit' | 'withdrawal'): void {
    this.filtroAttivo = filtro;
  }

  private updateChartData(): void {
    if (!this.movimenti || this.movimenti.length === 0) return;

    const cronologico = [...this.movimenti].reverse();

    this.lineChartData.labels = cronologico.map(m => {
      const date = new Date(m.created_at);
      return date.toLocaleDateString('it-IT', { day: '2-digit', month: '2-digit' });
    });

    let currentBalance = 0;
    this.lineChartData.datasets[0].data = cronologico.map(m => {
      const amount = Math.abs(Number(m.amount));
      if (m.type === 'withdrawal') {
        currentBalance -= amount;
      } else {
        currentBalance += amount;
      }
      return currentBalance;
    });

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
