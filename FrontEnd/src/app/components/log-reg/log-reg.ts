import { Component, OnInit, NgZone } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { Router } from '@angular/router'; // Importiamo il Router per cambiare pagina
import { environment } from '../../../environments/environment.development';

declare var google: any;

@Component({
  selector: 'log-reg',
  standalone: true,
  imports: [CommonModule, FormsModule],
  templateUrl: './log-reg.html',
  styleUrl: './log-reg.css'
})
export class LogReg implements OnInit {
  isLoginMode = true;

  username = '';
  email = '';
  password = '';

  constructor(
    private router: Router,
    private ngZone: NgZone
  ) {}

  ngOnInit() {
    if (typeof google !== 'undefined') {
      google.accounts.id.initialize({
        client_id: environment.googleClientId,
        callback: this.handleGoogleResponse.bind(this)
      });
    } else {
      console.error('Google Auth SDK not detected. Make sure to include the script in index.html');
    }
  }

  toggleMode() {
    this.isLoginMode = !this.isLoginMode;
  }

  onSubmit() {
    if (this.isLoginMode) {
      console.log('Logging in processing:', { email: this.email, password: this.password });
    } else {
      console.log('Registration recording:', { username: this.username, email: this.email, password: this.password });
    }
  }


  loginWithGoogle() {
    console.log('Triggering Google Sign-In prompt...');
    if (typeof google !== 'undefined') {
      google.accounts.id.prompt();
    }
  }

  private handleGoogleResponse(response: any) {

    const idToken = response.credential;
    console.log('Google Sign-In Successful! Token:', idToken);

    //ATTENZIONE!!! aggiungere logica per inviare il token al backend per poterlo controllare

    //in fase di development viene utilizzato il comando sottostante per forzare il cambio pagina
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
