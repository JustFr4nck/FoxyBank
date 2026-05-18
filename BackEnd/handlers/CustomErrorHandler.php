<?php 

use Psr\Http\Message\ServerRequestInterface as Request;
use Throwable;
use Slim\Exception\HttpException;
use Slim\Psr7\Response;

class CustomErrorHandler
{
    public function __invoke(
        Request $request,
        Throwable $exception,
        bool $displayErrorDetails,
        bool $logErrors,
        bool $logErrorDetails
    ) {
        $response = new Response();
        
        
        $statusCode = 500;
        if ($exception instanceof HttpException) {
            $statusCode = $exception->getCode();
        }

        $payload = [
            'status' => 'error',
            'message' => $exception->getMessage()
        ];

       
        if ($displayErrorDetails) {
            $payload['error_details'] = [
                'type' => get_class($exception),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTrace()
            ];
        }

        $response->getBody()->write(json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
        
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($statusCode);
    }
}

?>