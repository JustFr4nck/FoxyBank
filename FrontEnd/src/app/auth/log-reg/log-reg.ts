import { Component, OnInit, NgZone } from '@angular/core';
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
    private ngZone: NgZone
  ) {}

  ngOnInit() {
    // Spazio pronto per l'inizializzazione dell'SDK di Google se necessario (es. google.accounts.id.initialize)
  }

  loginWithGoogle() {
    // Qui andrà la logica per far partire il flusso di login di Google
    // Es: questa funzione può invocare il click sul bottone nativo invisibile di Google o aprire una popup custom
  }

  private handleGoogleResponse(response: any) {
    // Qui gestirai il token ricevuto da Google
    const idToken = response.credential;
    console.log('Google Sign-In Successful! Token:', idToken);

    // TODO: implementare la logica custom per inviare il token al backend

    // Reindirizzamento di test (puoi spostarlo all'interno del successo del tuo servizio futuro)
    this.ngZone.run(() => {
      this.router.navigate(['/']);
    });
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
