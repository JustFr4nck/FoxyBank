<?php
error_reporting(0);
header('Content-Type: application/json');
use Slim\Factory\AppFactory;
use Google\Client as GoogleClient;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/controllers/MovimentiController.php';
require __DIR__ . '/controllers/SaldoController.php';
require __DIR__ . '/controllers/AccountController.php';
require __DIR__ . '/handlers/CustomErrorHandler.php';
require __DIR__ . '/controllers/logRegController.php';


$app = AppFactory::create();

// ⚠️ Assicurati che le sessioni siano attive prima delle rotte
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$protectedRouteMiddleware = function (Request $request, Response $response, $handler) {
    if (!isset($_SESSION['user'])) {
        $response = new \Slim\Psr7\Response();
        // Se non è loggato, rimanda alla rotta di autenticazione iniziale
        return $response->withHeader('Location', '/auth/google')->withStatus(302);
    }
    /*
    if ($request->getUri()->getPath() != '/accounts/' . $_SESSION["user_id"]) {
        return $response->withHeader('Location', '/auth/google')->withStatus(302);
    }
        */
    return $handler->handle($request);
};

$errorMiddleware = $app->addErrorMiddleware(false, true, true);
$customErrorHandler = new CustomErrorHandler();

// Imposta l'handler personalizzato come default
$errorMiddleware->setDefaultErrorHandler($customErrorHandler);


// Movimenti
$app->get('/accounts/{idAccount}/transactions', "MovimentiController:index")->add($protectedRouteMiddleware);
$app->get('/accounts/{idAccount}/transactions/{idTransaction}', "MovimentiController:show")->add($protectedRouteMiddleware);
$app->post('/accounts/{idAccount}/deposit', "MovimentiController:create")->add($protectedRouteMiddleware);
$app->post('/accounts/{idAccount}/withdrawals', "MovimentiController:remove")->add($protectedRouteMiddleware);
$app->put('/accounts/{idAccount}/transactions/{idTransaction}', "MovimentiController:update")->add($protectedRouteMiddleware);
$app->delete('/accounts/{idAccount}/transactions/{idTransaction}', "MovimentiController:destroy")->add($protectedRouteMiddleware);

//Account
$app->get('/accounts/{idAccount}/user', "AccountController:index")->add($protectedRouteMiddleware);
$app->put('/accounts/{idAccount}/name', "AccountController:updateNik")->add($protectedRouteMiddleware);
$app->put('/accounts/{idAccount}/image', "AccountController:updateImg")->add($protectedRouteMiddleware);

// Saldo
$app->get('/accounts/{idAccount}/balance', "SaldoController:index")->add($protectedRouteMiddleware);

// Conversione del saldo
$app->get('/accounts/{idAccount}/balance/convert/fiat', "SaldoController:convert_to_fiat")->add($protectedRouteMiddleware);
$app->get('/accounts/{idAccount}/balance/convert/crypto', "SaldoController:convert_to_crypto")->add($protectedRouteMiddleware);


// Inizializzazione centralizzata del client di Google 
function getGoogleClient(): GoogleClient 
{
    $client = new GoogleClient();
    $client->setClientId('');
    $client->setClientSecret('');
    $client->setRedirectUri('http://localhost:80/auth/google/callback');
    
    // Specifichiamo gli scope per l'accesso al profilo e all'email dell'utente
    $client->addScope('email');
    $client->addScope('profile');
    
    return $client;
}

/*ROTTA 1: Reindirizzamento dell'utente a Google per il Login*/
$app->get('/auth/google', "logRegController:redirectToGoogle");

/*ROTTA 2: Callback gestita da Google dopo l'autorizzazione dell'utente*/
$app->get('/auth/google/callback', "logRegController:userAuth");

$app->run();

?>
