import { HttpInterceptorFn } from '@angular/common/http';

export const credentialsInterceptor: HttpInterceptorFn = (req, next) => {
  //to clone session cookies
  const secureReq = req.clone({
    withCredentials: true
  });
  return next(secureReq);
};
