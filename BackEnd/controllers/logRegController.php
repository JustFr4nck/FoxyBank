<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Google\Client as GoogleClient;

class LogRegController
{

    private $mysqli;

    public function __construct()
    {
        $this->mysqli =  new MovimentiMethods();
    }

    public function redirectToGoogle(Request $request, Response $response) {
    $client = getGoogleClient();
    
    $state = bin2hex(random_bytes(16));
    $_SESSION['oauth2state'] = $state;
    $client->setState($state);
    
    $authUrl = $client->createAuthUrl();
    
    return $response->withHeader('Location', $authUrl)->withStatus(302);
    }

    public function userAuth(Request $request, Response $response) {
    $queryParams = $request->getQueryParams();
    $client = getGoogleClient();

    // 1. Verifica dello stato per sicurezza anti-CSRF
    if (empty($queryParams['state']) || ($queryParams['state'] !== ($_SESSION['oauth2state'] ?? ''))) {
        unset($_SESSION['oauth2state']);
        $response->getBody()->write("Stato non valido (Attacco CSRF intercettato).");
        return $response->withStatus(400);
    }

    // 2. Controllo presenza codice di autorizzazione
    if (isset($queryParams['code'])) {
        try {
            // Scambio del codice con il token di accesso
            $token = $client->fetchAccessTokenWithAuthCode($queryParams['code']);
            $client->setAccessToken($token);
            
            // Richiesta dei dettagli dell'utente loggato
            $googleService = new \Google\Service\Oauth2($client);
            $userInfo = $googleService->userinfo->get();

            // 3. Elaborazione dei dati utente ricevuti
            $email = $userInfo->getEmail();
            $name = $userInfo->getName();
            $googleId = $userInfo->getId();

            // Salva l'utente in sessione o esegui logiche sul database (es. registrazione)
            $_SESSION['user'] = [
                'id' => $googleId,
                'name' => $name,
                'email' => $email
            ];


            $stmt = $this->mysqli->getConnection()->prepare("SELECT * FROM accounts WHERE google_id = ?");
            $stmt->bind_param("s", $googleId);
            $stmt->execute();

            $result = $stmt->get_result()->fetch_assoc();

            

            if($result){
                $_SESSION["user_id"] = $result["id"];

            }

            // Reindirizza l'utente alla dashboard protetta
            return $response->withHeader('Location', 'http://localhost:4200/')->withStatus(302);

        } catch (\Exception $e) {
            $response->getBody()->write("Errore durante l'autenticazione: " . $e->getMessage());
            return $response->withStatus(500);
        }
    }

    $response->getBody()->write("Codice di autorizzazione non fornito.");
    return $response->withStatus(400);
    }


}
