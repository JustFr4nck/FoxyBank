<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Google\Client as GoogleClient;

class LogRegController
{
    private $mysqli;

    public function __construct()
    {
        $this->mysqli = new MovimentiMethods();
    }

    public function redirectToGoogle(Request $request, Response $response, array $args)
    {
        $client = getGoogleClient();

        $state = bin2hex(random_bytes(16));
        $_SESSION['oauth2state'] = $state;
        $client->setState($state);

        $authUrl = $client->createAuthUrl();

        return $response->withHeader('Location', $authUrl)->withStatus(302);
    }

    public function userAuth(Request $request, Response $response, array $args)
    {
        $queryParams = $request->getQueryParams();
        $client = getGoogleClient();

        // 1. Verifica dello stato per sicurezza anti-CSRF
        if (empty($queryParams['state']) || ($queryParams['state'] !== ($_SESSION['oauth2state'] ?? ''))) {
            unset($_SESSION['oauth2state']);
            $response->getBody()->write("Invalid state (Attack CSRF blocked).");
            return $response->withStatus(400);
        }

        // 2. Controllo presenza codice di autorizzazione
        if (isset($queryParams['code'])) {
            try {
                // Scambio del codice con il token di acesso
                $token = $client->fetchAccessTokenWithAuthCode($queryParams['code']);
                $client->setAccessToken($token);

                // Richiesta dei dettagli dell'utente loggato
                $googleService = new \Google\Service\Oauth2($client);
                $userInfo = $googleService->userinfo->get();

                // 3. Elaborazione dei dati utente ricevuti
                $email = $userInfo->getEmail();
                $name = $userInfo->getName();
                $googleId = $userInfo->getId();
                $picture = $userInfo->getPicture();

                // CORREZIONE 1: Usiamo la chiave 'google_id' coerentemente con gli altri controller
                $_SESSION['user'] = [
                    'google_id' => $googleId,
                    'name' => $name,
                    'email' => $email,
                    'picture' => $picture
                ];

                $db = $this->mysqli->getConnection();
                $stmt = $db->prepare("SELECT * FROM accounts WHERE google_id = ?");
                $stmt->bind_param("s", $googleId);
                $stmt->execute();

                $result = $stmt->get_result()->fetch_assoc();

                // CORREZIONE 2: Gestione lineare di $userId per evitare sovrascritture vuote
                $accountId = null;

                if ($result) {
                    // Se l'account esiste già, recuperiamo il suo ID reale dal DB
                    $accountId = (int)$result["id"];
                } else {
                    // Se l'account non esiste, lo creiamo da zero
                    $insertStmt = $db->prepare("INSERT INTO accounts (user_name, email, profile_image, google_id) VALUES (?, ?, ?, ?)");
                    $insertStmt->bind_param("ssss", $name, $email, $picture, $googleId);
                    $insertStmt->execute();
                    $accountId = (int)$insertStmt->insert_id;
                }

                // Popoliamo il resto delle variabili di sessione usando la variabile sicura
                $_SESSION["user_id"] = $accountId;
                $_SESSION["user_name"] = $name;

                // Rimuoviamo lo stato OAuth monouso
                unset($_SESSION['oauth2state']);

                // Reindirizza l'utente al frontend Angular
                return $response->withHeader('Location', 'http://localhost:4200/')->withStatus(302);
            } catch (\Exception $e) {
                $response->getBody()->write("Errore durante l'autenticazione: " . $e->getMessage());
                return $response->withStatus(500);
            }
        }

        $response->getBody()->write("Codice di autorizzazione non fornito.");
        return $response->withStatus(400);
    }

    public function logout(Request $request, Response $response, array $args)
    {
        $_SESSION = array();

        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }

        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }

        $response->getBody()->write(json_encode(["message" => "Logout effettuato con successo"]));
        return $response->withHeader("Content-type", "application/json")->withStatus(200);
    }
}
