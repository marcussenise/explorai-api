<?php
require_once __DIR__ . '/../config/firebase.php';

class UserController {
    public function getProfile($request) {
        $userUid = $request['user']['uid'];
        echo json_encode([
            'statusCode' => 200,
            'payload' => [
                'message' => "Dados protegidos do perfil.",
                'uid' => $userUid,
                'email' => $request['user']['email']
            ]
        ]);
    }

    public function logout($request) {
      
        $userUid = $request['user']['uid'] ?? null;

        if (!$userUid) {
            http_response_code(401);
            echo json_encode(['error' => 'UID do usuário ausente.']);
            return;
        }

        try {
            $auth = get_firebase_auth();
            
            $auth->revokeRefreshTokens($userUid);

            echo json_encode([
                'statusCode' => 200,
                'payload' => ['message' => 'Sessão encerrada com sucesso em todos os dispositivos.']
            ]);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Erro ao encerrar a sessão.']);
        }
    }
    public function deleteAccount($request) {
        $userUid = $request['user']['uid'];
        try {
            $auth = get_firebase_auth();
            $auth->deleteUser($userUid);
            $firestore = get_firebase_firestore();
            $firestore->database()->collection('usuarios')->document($userUid)->delete();
            echo json_encode([
                'statusCode' => 200,
                'payload' => ['message' => 'Conta deletada com sucesso.']
            ]);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Erro ao deletar conta.']);
        }
    }

    public function register($request) {
        $email = $request['email'] ?? null;
        $password = $request['password'] ?? null;
        $displayName = $request['name'] ?? null;

        if (empty($email) || empty($password) || empty($displayName)) {
            http_response_code(400);
            echo json_encode(['error' => 'Dados incompletos. E-mail, nome e senha são obrigatórios.']);
            return;
        }

        try {
            $auth = get_firebase_auth();
            $user = $auth->createUser([
                'email' => $email,
                'password' => $password,
                'displayName' => $displayName
            ]);

            $firestore = get_firebase_firestore();
            $firestore->database()->collection('usuarios')->document($user->uid)->set([
                'statusConta' => 'ativo',
                'createdAt' => time()
            ]);

            echo json_encode([
                'statusCode' => 201,
                'payload' => [
                    'message' => 'Usuário registrado com sucesso!',
                    'uid' => $user->uid
                ]
            ]);
        } catch (\Kreait\Firebase\Exception\Auth\EmailExists $e) {
            http_response_code(409);
            echo json_encode(['error' => 'O e-mail já está em uso.']);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Erro interno ao registrar.']);
        }
    }

    public function forgotPassword($request) {
        $email = $request['email'] ?? null;
        if (empty($email)) {
            http_response_code(400);
            echo json_encode(['error' => 'E-mail ausente.']);
            return;
        }
        try {
            $auth = get_firebase_auth();
            $auth->sendPasswordResetLink($email);
            echo json_encode([
                'statusCode' => 200,
                'payload' => ['message' => 'Link de redefinição de senha enviado.']
            ]);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Não foi possível enviar o link.']);
        }
    }

    public function login($request) {
        $email = $request['email'] ?? null;
        $password = $request['password'] ?? null;

        if (empty($email) || empty($password)) {
            http_response_code(400);
            echo json_encode(['error' => 'E-mail e senha são obrigatórios.']);
            return;
        }

        try {
            $apiKey = $_ENV['FIREBASE_API_KEY'];
            $url = "https://identitytoolkit.googleapis.com/v1/accounts:signInWithPassword?key={$apiKey}";
            $postData = [
                'email' => $email,
                'password' => $password,
                'returnSecureToken' => true
            ];

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            http_response_code($httpCode);
            echo $response;
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Erro interno ao tentar logar.']);
        }
    }

    public function updateProfile($request) {
        $uid = $request['user']['uid'] ?? null;
        $updates = $request['data'] ?? [];

        if (!$uid || empty($updates)) {
            http_response_code(400);
            echo json_encode(['error' => 'Dados inválidos.']);
            return;
        }

        try {
            $firestore = get_firebase_firestore();
            $firestore->database()->collection('usuarios')->document($uid)->update($updates);
            echo json_encode([
                'statusCode' => 200,
                'payload' => ['message' => 'Perfil atualizado com sucesso.']
            ]);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Erro ao atualizar perfil.']);
        }
    }
}
