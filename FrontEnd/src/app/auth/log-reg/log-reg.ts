import { Component, OnInit} from '@angular/core';
import { CommonModule } from '@angular/common';
import { Router } from '@angular/router';

@Component({
  selector: 'log-reg',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './log-reg.html',
  styleUrl: './log-reg.css'
})
export class LogReg implements OnInit {

  constructor(
    private router: Router,

  ) {}

  ngOnInit() {

  }




  moneyRain = Array.from({ length: 25 }, (_, i) => ({
    id: i,
    char: Math.random() > 0.5 ? '0' : '1',
    left: Math.floor(Math.random() * 100),
    delay: Math.random() * 8,
    duration: 6 + Math.random() * 4,
    size: 14 + Math.random() * 12,
    blur: Math.random() > 0.7 ? '2px' : '0px',
    zIndex: -1
  }));
}
