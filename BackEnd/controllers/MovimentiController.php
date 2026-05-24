<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

require_once __DIR__ . '/../methods/MovimentiMethods.php';

class MovimentiController
{
  private $mysqli;

  public function __construct()
  {
    $this->mysqli = new MovimentiMethods();
  }

  // Funzione di supporto interna per recuperare l'ID della sessione attiva
  private function getAuthenticatedAccountId($db)
  {
    // controlla l'esistenza della funzione
    if (!isset($_SESSION['user']['google_id'])) {
      return null;
    }
    
    //prende i parametri
    $googleId = $_SESSION['user']['google_id'];
    $stmt = $db->prepare("SELECT id FROM accounts WHERE google_id = ?");
    $stmt->bind_param("s", $googleId);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    
    return $res ? (int)$res['id'] : null;
  }

  // ? GET /accounts/my-account/transactions
  public function index(Request $request, Response $response, array $args)
  {
    $db = $this->mysqli->getConnection();
    $accountId = $this->getAuthenticatedAccountId($db);

    if (!$accountId) {
      $response->getBody()->write(json_encode(["error" => "permesso negato"]));
      return $response->withHeader("Content-type", "application/json")->withStatus(401);
    }

    $query = "
        SELECT t.*, a.currency 
        FROM transactions t
        INNER JOIN accounts a ON t.account_id = a.id
        WHERE a.id = ?
        ORDER BY t.created_at DESC
    ";

    $stmt = $db->prepare($query);
    $stmt->bind_param("i", $accountId);
    $stmt->execute();
    $results = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    $response->getBody()->write(json_encode($results));
    return $response->withHeader("Content-type", "application/json")->withStatus(200);
  }

  // ? GET /accounts/my-account/transactions/{idTransaction}
  public function show(Request $request, Response $response, array $args)
  {
    $db = $this->mysqli->getConnection();
    $accountId = $this->getAuthenticatedAccountId($db);
    $idTrans = $args['idTransaction'];

    if (!$accountId) {
      $response->getBody()->write(json_encode(["error" => "permeso negato"]));
      return $response->withHeader("Content-type", "application/json")->withStatus(401);
    }

    $stmt = $db->prepare("SELECT * FROM transactions WHERE id = ? AND account_id = ?");
    $stmt->bind_param("ii", $idTrans, $accountId);
    $stmt->execute();
    $results = $stmt->get_result()->fetch_assoc();

    if (!$results) {
      $response->getBody()->write(json_encode("ERRORE: movimento non trovato"));
      return $response->withHeader("Content-type", "application/json")->withStatus(404);
    }

    $response->getBody()->write(json_encode($results));
    return $response->withHeader("Content-type", "application/json")->withStatus(200);
  }

  // ? POST /accounts/my-account/deposit
  public function create(Request $request, Response $response, array $args)
  {
    $db = $this->mysqli->getConnection();
    $accountId = $this->getAuthenticatedAccountId($db);

    if (!$accountId) {
      $response->getBody()->write(json_encode(["error" => "permission denied"]));
      return $response->withHeader("Content-type", "application/json")->withStatus(401);
    }

    $body = json_decode($request->getBody(), true);
    $importo = $body['amount'] ?? 0;
    $descrizione = $body['description'] ?? 'nessuna descrizione sul deposito';

    if ($importo <= 0) {
      $response->getBody()->write(json_encode(["ERRORE!" => "L'importo deve essere maggiore di zero"]));
      return $response->withHeader("Content-type", "application/json")->withStatus(422);
    }

    $stmt = $db->prepare("INSERT INTO transactions (`account_id`, `type`, `amount`, `description`) VALUES (?, 'deposit', ?, ?)");
    $stmt->bind_param("ids", $accountId, $importo, $descrizione);

    if ($stmt->execute()) {
      $response->getBody()->write(json_encode(["message" => "Deposito effettuato"]));
      return $response->withHeader("Content-type", "application/json")->withStatus(201);
    } else {
      $response->getBody()->write(json_encode(["error" => "Errore durante l'operazione"]));
      return $response->withStatus(502);
    }
  }

  // ? POST /accounts/my-account/withdrawals
  public function remove(Request $request, Response $response, array $args)
  {
    $db = $this->mysqli->getConnection();
    $accountId = $this->getAuthenticatedAccountId($db);

    if (!$accountId) {
      $response->getBody()->write(json_encode(["error" => "permission denied"]));
      return $response->withHeader("Content-type", "application/json")->withStatus(401);
    }

    $body = json_decode($request->getBody(), true);
    $importo = $body['amount'] ?? 0;
    $descrizione = $body['description'] ?? 'nessuna descrizione sul prelievo';

    if ($importo <= 0) {
      $response->getBody()->write(json_encode(["ERRORE!" => "L'importo deve essere maggiore di zero"]));
      return $response->withHeader("Content-type", "application/json")->withStatus(422);
    }

    // Calcolo saldo reale aggregato (Somma depositi - Somma prelievi)
    $stmt = $db->prepare("SELECT SUM(CASE WHEN type = 'deposit' THEN amount ELSE -amount END) as balance FROM transactions WHERE account_id = ?");
    $stmt->bind_param("i", $accountId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $saldo_attuale = floatval($row['balance'] ?? 0);

    if ($importo > $saldo_attuale) {
      $response->getBody()->write(json_encode(["ERRORE!" => "L'importo richiesto non può essere superiore del saldo disponibile"]));
      return $response->withHeader("Content-type", "application/json")->withStatus(422);
    }

    $stmt = $db->prepare("INSERT INTO transactions (account_id, type, amount, description) VALUES (?, 'withdrawal', ?, ?)");
    $stmt->bind_param("ids", $accountId, $importo, $descrizione);

    if ($stmt->execute()) {
      $response->getBody()->write(json_encode(["message" => "Prelievo effettuato"]));
      return $response->withHeader("Content-type", "application/json")->withStatus(201);
    } else {
      $response->getBody()->write(json_encode(["error" => "Errore durante l'operazione"]));
      return $response->withStatus(502);
    }
  }

  // ? PUT /accounts/my-account/transactions/{idTransaction}
  public function update(Request $request, Response $response, array $args)
  {
    $db = $this->mysqli->getConnection();
    $accountId = $this->getAuthenticatedAccountId($db);
    $idTransaction = $args["idTransaction"];

    if (!$accountId) {
      $response->getBody()->write(json_encode(["error" => "permission denied"]));
      return $response->withHeader("Content-type", "application/json")->withStatus(401);
    }

    $body = json_decode($request->getBody(), true);
    $newDescrizione = $body["description"] ?? null;

    if (!$newDescrizione) {
      $response->getBody()->write(json_encode(["ERRORE:" => "Descrizione da aggiornare mancante alla richiesta"]));
      return $response->withHeader("Content-type", "application/json")->withStatus(400);
    }

    $stmt = $db->prepare("UPDATE transactions SET description = ? WHERE id = ? AND account_id = ?");
    $stmt->bind_param("sii", $newDescrizione, $idTransaction, $accountId);

    if ($stmt->execute()) {
      $response->getBody()->write(json_encode(["message" => "Descrizione aggiornata"]));
      return $response->withHeader("Content-type", "application/json")->withStatus(201);
    } else {
      $response->getBody()->write(json_encode(["ERRORE:" => "Errore durante l'operazione"]));
      return $response->withStatus(502);
    }
  }

  // ? DELETE /accounts/my-account/transactions/{idTransaction}
  public function destroy(Request $request, Response $response, array $args)
  {
    $db = $this->mysqli->getConnection();
    $accountId = $this->getAuthenticatedAccountId($db);
    $idTransaction = $args["idTransaction"];

    if (!$accountId) {
      $response->getBody()->write(json_encode(["error" => "permission denied"]));
      return $response->withHeader("Content-type", "application/json")->withStatus(401);
    }

    $stmt = $db->prepare("DELETE FROM transactions WHERE id = ? AND account_id = ?");
    $stmt->bind_param("ii", $idTransaction, $accountId);

    if ($stmt->execute()) {
      $response->getBody()->write(json_encode(["message" => "Transazione eliminata con successo"]));
      return $response->withHeader("Content-type", "application/json")->withStatus(201);
    } else {
      $response->getBody()->write(json_encode(["ERRORE:" => "Errore durante l'operazione"]));
      return $response->withStatus(502);
    }
  }
}