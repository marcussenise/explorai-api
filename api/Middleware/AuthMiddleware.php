<?php
require_once __DIR__ . '/../config/firebase.php';

class AuthMiddleware {

    public function handle(array &$request): void {
        $headers = getallheaders();
        $idToken = $this->getBearerToken($headers);

        try {
            $verifiedIdToken = $this->verifyToken($idToken);

            $request['user'] = [
                'uid' => $verifiedIdToken->claims()->get('sub'),
                'email' => $verifiedIdToken->claims()->get('email')
            ];

        } catch (\Kreait\Firebase\Exception\Auth\IdTokenExpired $e) {
            $this->respondWithError('Token expirado.', 401);
        } catch (\Throwable $e) {
            error_log('AuthMiddleware error: ' . $e->getMessage());
            $this->respondWithError('Token inválido.', 401);
        }
    }

    private function getBearerToken(array $headers): string {
        $authHeader = null;

        foreach ($headers as $key => $value) {
            if (strtolower($key) === 'authorization') {
                $authHeader = $value;
                break;
            }
        }

        if (!$authHeader) {
            $this->respondWithError('Cabeçalho de autorização ausente.', 401);
        }

        $idToken = '';
        if (sscanf($authHeader, 'Bearer %s', $idToken) !== 1) {
            $this->respondWithError('Token mal formatado.', 401);
        }

        return $idToken;
    }

    private function verifyToken(string $idToken) {
        $auth = get_firebase_auth();
        return $auth->verifyIdToken($idToken);
    }

    private function respondWithError(string $message, int $code = 401): void {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode(['error' => $message]);
        exit();
    }
}
