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

$app->getRouteCollector()->setDefaultInvocationStrategy(new \Slim\Handlers\Strategies\RequestResponse());

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

use Psr\Http\Server\RequestHandlerInterface as Handler;


// protezione del middleware per proteggere dati oauth
$protectedRouteMiddleware = function (Request $request, Handler $handler) {
    if (!isset($_SESSION['user'])) {
        $response = new \Slim\Psr7\Response();
        return $response
            ->withHeader('Location', '/auth/google')
            ->withStatus(302);
    }
    return $handler->handle($request);
};

$errorMiddleware = $app->addErrorMiddleware(false, true, true);
$customErrorHandler = new CustomErrorHandler();
$errorMiddleware->setDefaultErrorHandler($customErrorHandler);


// Movimenti
$app->get('/accounts/my-account/transactions', "MovimentiController:index")->add($protectedRouteMiddleware);
$app->get('/accounts/my-account/transactions/{idTransaction}', "MovimentiController:show")->add($protectedRouteMiddleware);
$app->post('/accounts/my-account/deposit', "MovimentiController:create")->add($protectedRouteMiddleware);
$app->post('/accounts/my-account/withdrawals', "MovimentiController:remove")->add($protectedRouteMiddleware);
$app->put('/accounts/my-account/transactions/{idTransaction}', "MovimentiController:update")->add($protectedRouteMiddleware);
$app->delete('/accounts/my-account/transactions/{idTransaction}', "MovimentiController:destroy")->add($protectedRouteMiddleware);

// Account (Modifica anche all'interno di AccountController per usare $_SESSION in modo analogo)
$app->get('/accounts/my-account/user', "AccountController:index")->add($protectedRouteMiddleware);

// Saldo
$app->get('/accounts/my-account/balance', "SaldoController:index")->add($protectedRouteMiddleware);

// Conversione del saldo
$app->get('/accounts/my-account/balance/convert/fiat', "SaldoController:convert_to_fiat")->add($protectedRouteMiddleware);
$app->get('/accounts/my-account/balance/convert/crypto', "SaldoController:convert_to_crypto")->add($protectedRouteMiddleware);


// Inizializzazione centralizzata del client di Google 
function getGoogleClient(): GoogleClient 
{
    $client = new GoogleClient();
    $client->setClientId('');
    $client->setClientSecret('');
    $client->setRedirectUri('http://localhost:80/auth/google/callback');
    
    $client->addScope('email');
    $client->addScope('profile');
    
    return $client;
}

/*ROTTA 1: Reindirizzamento dell'utente a Google per il Login*/
$app->get('/auth/google', "logRegController:redirectToGoogle");

/*ROTTA 2: Callback gestita da Google dopo l'autorizzazione dell'utente*/
$app->get('/auth/google/callback', "logRegController:userAuth");

/* ROTTA 3: Sloggarsi e distruggere la sessione */
$app->get('/auth/logout', "LogRegController:logout");



$app->run();