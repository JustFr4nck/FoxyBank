import { AuthConfig } from "angular-oauth2-oidc";

export const googleAuthConfig: AuthConfig = {
  issuer: 'https://accounts.google.com',
  strictDiscoveryDocumentValidation: false,

  redirectUri: window.location.origin + '/',
  clientId: '187757474717-rtvq4nd4ci28sd6vir810tjiig59bm18.apps.googleusercontent.com',
  scope: 'openid profile bank',

  customQueryParams: {
    prompt: 'select_account'
  },
  showDebugInformation: true,
};
