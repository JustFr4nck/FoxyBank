<?php
error_reporting(0);
header('Content-Type: application/json');
use Slim\Factory\AppFactory;

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/controllers/MovimentiController.php';
require __DIR__ . '/controllers/SaldoController.php';
require __DIR__ . '/controllers/AccountController.php';
require __DIR__ . '/handlers/CustomErrorHandler.php';
require __DIR__ . '/controllers/logRegController.php';


$app = AppFactory::create();


$errorMiddleware = $app->addErrorMiddleware(false, true, true);
$customErrorHandler = new CustomErrorHandler();

// Imposta l'handler personalizzato come default
$errorMiddleware->setDefaultErrorHandler($customErrorHandler);


// Movimenti
$app->get('/accounts/{idAccount}/transactions', "MovimentiController:index");
$app->get('/accounts/{idAccount}/transactions/{idTransaction}', "MovimentiController:show");
$app->post('/accounts/{idAccount}/deposit', "MovimentiController:create");
$app->post('/accounts/{idAccount}/withdrawals', "MovimentiController:remove");
$app->put('/accounts/{idAccount}/transactions/{idTransaction}', "MovimentiController:update");
$app->delete('/accounts/{idAccount}/transactions/{idTransaction}', "MovimentiController:destroy");

//login or register
$app->post('/login', "LogRegController:check");

//Account
$app->get('/accounts/{idAccount}/user', "AccountController:index");
$app->put('/accounts/{idAccount}/name', "AccountController:updateNik");
$app->put('/accounts/{idAccount}/image', "AccountController:updateImg");

// Saldo
$app->get('/accounts/{idAccount}/balance', "SaldoController:index");

// Conversione del saldo
$app->get('/accounts/{idAccount}/balance/convert/fiat', "SaldoController:convert_to_fiat");
$app->get('/accounts/{idAccount}/balance/convert/crypto', "SaldoController:convert_to_crypto");


$app->run();
