<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

require_once __DIR__ . '/../methods/MovimentiMethods.php';

class SaldoController
{
    private $mysqli;

    public function __construct()
  {
    $this->mysqli = new MovimentiMethods();
  }

    private function getAuthenticatedAccountId($db)
    {
        if (!isset($_SESSION['user']['google_id'])) {
            return null;
        }
        
        $googleId = $_SESSION['user']['google_id'];
        $stmt = $db->prepare("SELECT id FROM accounts WHERE google_id = ?");
        $stmt->bind_param("s", $googleId);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        
        return $res ? (int)$res['id'] : null;
    }

    // GET /accounts/my-account/balance
    public function index(Request $request, Response $response, array $args){
        $db = $this->mysqli->getConnection();
        $accountId = $this->getAuthenticatedAccountId($db);

        if (!$accountId) {
            $response->getBody()->write(json_encode(['error' => 'Unauthorized']));
            return $response->withHeader("Content-type", "application/json")->withStatus(410);
        }

        $result = $db->query("SELECT SUM(CASE WHEN type = 'deposit' THEN amount ELSE -amount END) as balance FROM transactions WHERE account_id = $accountId");
        $row = $result->fetch_assoc();
        $balance = (float)($row['balance'] ?? 0.00);

        $response->getBody()->write(json_encode([
            'account_id' => $accountId,
            'balance' => $balance
        ]));
        return $response->withHeader("Content-type", "application/json")->withStatus(200);
    }


    // GET /accounts/my-account/balance/convert/fiat?to=USD
    public function convert_to_fiat(Request $request, Response $response, array $args){
        $db = $this->mysqli->getConnection();
        $accountId = $this->getAuthenticatedAccountId($db);

        if (!$accountId) {
            $response->getBody()->write(json_encode(['error' => 'Unauthorized']));
            return $response->withHeader("Content-type", "application/json")->withStatus(401);
        }

        $params = $request->getQueryParams();
        $to = strtoupper($params['to'] ?? '');

        if (!$to) {
            $response->getBody()->write(json_encode(['error' => 'Missing target currency']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        $stmt = $db->prepare('SELECT id, currency FROM accounts WHERE id = ?');
        $stmt->bind_param('i', $accountId);
        $stmt->execute();
        $account = $stmt->get_result()->fetch_assoc();

        $from = strtoupper($account['currency']);

        $stmt = $db->prepare("
            SELECT
                COALESCE(SUM(CASE WHEN type = 'deposit' THEN amount ELSE 0 END), 0) -
                COALESCE(SUM(CASE WHEN type = 'withdrawal' THEN amount ELSE 0 END), 0) AS balance
            FROM transactions
            WHERE account_id = ?
        ");
        $stmt->bind_param('i', $accountId);
        $stmt->execute();
        $balance = $stmt->get_result()->fetch_assoc()['balance'] ?? 0;

        $url = "https://api.frankfurter.dev/v1/latest?base={$from}&symbols={$to}";
        $json = @file_get_contents($url);

        if ($json === false) {
            $response->getBody()->write(json_encode(['error' => 'External exchange API unavailable']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(502);
        }

        $data = json_decode($json, true);

        if (!isset($data['rates'][$to])) {
            $response->getBody()->write(json_encode(['error' => 'Target currency not supported']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        $rate = $data['rates'][$to];
        $converted = round($balance * $rate, 2);

        $response->getBody()->write(json_encode([
            'account_id' => $accountId,
            'provider' => 'Frankfurter',
            'conversion_type' => 'fiat',
            'from_currency' => $from,
            'to_currency' => $to,
            'original_balance' => $balance,
            'converted_balance' => $converted,
            'rate' => $rate,
            'date' => $data['date'] ?? null
        ]));

        return $response->withHeader('Content-Type', 'application/json');
    }

    // GET /accounts/my-account/balance/convert/crypto?to=BTC
    public function convert_to_crypto(Request $request, Response $response, array $args) {
        $db = $this->mysqli->getConnection();
        $accountId = $this->getAuthenticatedAccountId($db);
        
        if (!$accountId) {
            $response->getBody()->write(json_encode(['error' => 'Unauthorized']));
            return $response->withHeader("Content-type", "application/json")->withStatus(401);
        }

        $params = $request->getQueryParams();
        $to = strtoupper($params['to'] ?? '');

        if (!$to) {
            $response->getBody()->write(json_encode(['error' => 'Missing target crypto']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        $stmt = $db->prepare('SELECT currency FROM accounts WHERE id = ?');
        $stmt->bind_param('i', $accountId);
        $stmt->execute();
        $account = $stmt->get_result()->fetch_assoc();
        $from = strtoupper($account['currency']);

        $stmt = $db->prepare("
            SELECT 
                COALESCE(SUM(CASE WHEN type = 'deposit' THEN amount ELSE 0 END), 0) - 
                COALESCE(SUM(CASE WHEN type = 'withdrawal' THEN amount ELSE 0 END), 0) AS balance 
            FROM transactions 
            WHERE account_id = ?
        ");
        $stmt->bind_param('i', $accountId);
        $stmt->execute();
        $balance = $stmt->get_result()->fetch_assoc()['balance'] ?? 0;

        $marketSymbol = $to . $from; 
        $url = "https://api.binance.com/api/v3/ticker/price?symbol=" . $marketSymbol;
        $json = @file_get_contents($url);

        if ($json === false) {
            $response->getBody()->write(json_encode([
                'error' => "Market pair {$marketSymbol} not supported on Binance",
                'url_tested' => $url 
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(502);
        }

        $data = json_decode($json, true);
        $price = $data['price'] ?? 0;

        if ($price <= 0) {
            $response->getBody()->write(json_encode(['error' => 'Invalid price from Binance']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(502);
        }

        $converted = round($balance / $price, 8);

        $response->getBody()->write(json_encode([
            'account_id' => $accountId,
            'provider' => 'Binance',
            'conversion_type' => 'crypto',
            'from_currency' => $from,
            'to_crypto' => $to,
            'market_symbol' => $marketSymbol,
            'original_balance' => $balance,
            'price' => $price,
            'converted_amount' => $converted
        ]));

        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }
}