import { Injectable } from '@angular/core';
import { OAuthService } from 'angular-oauth2-oidc';
import { googleAuthConfig } from '../auth/google-auth.config';
import { HttpClient } from '@angular/common/http';

export interface TokenResponse {
  accessToken: string;
  refreshToken: string;
}

@Injectable({
  providedIn: 'root',
})
export class AuthService {
  private apiUrl = '/login';

  constructor(private oAuth: OAuthService, private http: HttpClient ){
    this.oAuth.configure(googleAuthConfig);
  };

  googleLogin(){
    this.oAuth.initCodeFlow(); //redirect to Google login page
  }

  async processGoogleLogin(){
    const result = await this.oAuth.loadDiscoveryDocumentAndTryLogin();
    if(!this.oAuth.hasValidAccessToken()) return null;
    const idToken = this.oAuth.getIdToken();

    return this.http.post(this.apiUrl, {idToken});
  }

  getCurrentUser(){
    const token = localStorage.getItem('access_token');
    if(!token) return null;

    const payload = JSON.parse(atob(token.split('.')[1]));

    return {
      username: payload["http://schemas.xmlsoap.org/ws/2005/05/identity/claims/name"]
    }
  }
}
