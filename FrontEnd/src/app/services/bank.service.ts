import { Injectable } from '@angular/core';
import { AccountBalance, ConversionBTC, ConversionUSD, Transaction } from '../models/saldo.model';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../environments/environment';

@Injectable({
  providedIn: 'root',
})
export class BankService {
  private apiUrl = '/accounts/my-account';

  constructor(private http: HttpClient) {}

  //balance
  getAccountBalance(): Observable<AccountBalance> {
    return this.http.get<AccountBalance>(`${this.apiUrl}/balance`);
  }

  //transactions list
  getTransactions(): Observable<Transaction[]> {
    return this.http.get<Transaction[]>(`${this.apiUrl}/transactions`);
  }

  //convert to USD
  getConvUSD(): Observable<ConversionUSD> {
    return this.http.get<ConversionUSD>(`${this.apiUrl}/balance/convert/fiat?to=USD`);
  }

  //convert to BTC
  getConvBTC(): Observable<ConversionBTC> {
    return this.http.get<ConversionBTC>(`${this.apiUrl}/balance/convert/crypto?to=BTC`);
  }

  //transaction details
  getTransactionDet(id: number): Observable<Transaction> {
    return this.http.get<Transaction>(`${this.apiUrl}/transactions/${id}`);
  }

  //update description
  updateDescTrans(id: number, newDescription: string): Observable<Transaction> {
    return this.http.put<Transaction>(`${this.apiUrl}/transactions/${id}`, {
      description: newDescription,
    });
  }

  //delete transaction
  deleteTrans(id: number): Observable<any> {
    return this.http.delete(`${this.apiUrl}/transactions/${id}`);
  }

  //withdrawals
  doWithdrawals(data: Partial<Transaction>): Observable<Transaction> {
    return this.http.post<Transaction>(`${this.apiUrl}/withdrawals`, data);
  }

  //deposit
  doDeposit(data: Partial<Transaction>): Observable<Transaction> {
    return this.http.post<Transaction>(`/deposit`, data);
  }

  //logout
  logout(): Observable<any> {
    return this.http.get(`/auth/logout`, { withCredentials: true, responseType: 'text' });
  }

  //account data
  getAccountUser(): Observable<any> {
  return this.http.get<any>('/accounts/my-account/user', { withCredentials: true });
}
}
