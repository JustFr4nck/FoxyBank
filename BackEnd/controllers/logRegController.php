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




    public function check(Response $response, Request $request, $args)
    {
        $mysqli = $this->mysqli->getConnection();
        $body = json_decode($request->getBody(), true);

        if (!isset($body['idToken'])) {
            $response->getBody()->write(json_encode(["error" => "Missing authentication token"]));
            return $response->withHeader("Content-type", "application/json")->withStatus(400);
        }

        $idToken = $body['idToken'];

        $CLIENT_ID = "187757474717-13jc4g4sejhf85mrn75g4jvj6l6opp4q.apps.googleusercontent.com";

        $client = new GoogleClient(['client_id' => $CLIENT_ID]);

        //verifica firma e scadenza del token
        try {
            $payload = $client->verifyIdToken($idToken);
        } catch (\Exception $e) {
            $payload = false; 
        }

        if (!$payload) {
            $response->getBody()->write(json_encode(["error" => "Handshake protocol mismatch. Invalid Token."]));
            return $response->withHeader("Content-type", "application/json")->withStatus(401);
        }

        
        $googleUserId = $payload['sub'];
        $email = $payload['email'];
        $name = $payload['name'] ?? 'Unknown Operator';

        
        $stmt = $mysqli->prepare("SELECT * FROM accounts WHERE google_id = ?");
        $stmt->bind_param("s", $googleUserId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();

        if (!$result) {
            
            //aggiungere utente se non esiste
        }

        //TODO implementare logica di stato

        $responseData = [
            "status" => "SUCCESS",
            "message" => "Identity cleared. Welcome back, operator.",
            "operator" => [
                "username" => $result['username'],
                "email" => $result['email']
            ]
        ];

        $response->getBody()->write(json_encode($responseData));
        return $response->withHeader("Content-type", "application/json")->withStatus(200);
    }
}
