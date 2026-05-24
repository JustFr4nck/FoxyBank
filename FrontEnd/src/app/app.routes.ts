import { LogReg } from './auth/log-reg/log-reg';
import { HomePage } from './pages/home-page/home-page';
import { Routes } from '@angular/router';
import { ListaMov } from './pages/lista-mov/lista-mov';
import { MovDetails } from './pages/mov-details/mov-details';
import { Prelievo } from './pages/prelievo/prelievo';
import { Deposito } from './pages/deposito/deposito';
import { SettingsPage } from './pages/settings-page/settings-page';


export const routes: Routes = [

    {path: "", component: HomePage, pathMatch: 'full'},
    {path: "login", component:LogReg},
    {path: "listaMovimenti", component: ListaMov},
    {path: "movDetails/:id", component: MovDetails},
    {path: "prelievo", component: Prelievo},
    {path: "deposito", component: Deposito},
    {path: "settings", component: SettingsPage},
    {path: "**", redirectTo: ""}

];
