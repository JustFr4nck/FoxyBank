<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

require_once __DIR__ . '/../methods/MovimentiMethods.php';

class AccountController
{
  private $mysqli;
  
  public function __construct(){
    $this->mysqli =  new MovimentiMethods();
  }

  public function index(Request $request, Response $response, array $args)
{
    if (!isset($_SESSION['user']['google_id'])) {
        $response->getBody()->write(json_encode(["error" => "permisso negato"]));
        return $response->withHeader("Content-type", "application/json")->withStatus(401);
    }

    $googleId = $_SESSION['user']['google_id'];
    $db = $this->mysqli->getConnection();

    $stmt = $db->prepare("SELECT * FROM accounts WHERE google_id = ?");
    $stmt->bind_param("s", $googleId);
    $stmt->execute();
    
    $accountData = $stmt->get_result()->fetch_assoc();

    if (!$accountData) {
        $response->getBody()->write(json_encode(["error" => "ERRORE: nessun account trovato"]));
        return $response->withHeader("Content-type", "application/json")->withStatus(404);
    }

    $response->getBody()->write(json_encode($accountData));
    return $response->withHeader("Content-type", "application/json")->withStatus(200);
}

}

?>