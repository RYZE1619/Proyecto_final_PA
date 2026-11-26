<?php

namespace App\Presentation\Repositories;

use App\Controllers\UsersController;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class UserRepository
{
    private function jsonResponse(Response $response, array $data, int $status = 200): Response
    {
        $response->getBody()->write(json_encode($data));
        return $response
            ->withStatus($status)
            ->withHeader('Content-Type', 'application/json');
    }

    private function extractToken(Request $request, ?array $body = null): string
    {
        $authHeader = $request->getHeaderLine('Authorization');
        if (str_starts_with($authHeader, 'Bearer ')) {
            return trim(substr($authHeader, 7));
        }

        if (!empty($authHeader)) {
            return trim($authHeader);
        }

        if ($body && !empty($body['token'])) {
            return trim((string) $body['token']);
        }

        return '';
    }

    public function login(Request $request, Response $response, $args): Response
    {
        try {
            $data = json_decode($request->getBody()->getContents(), true) ?? [];
            $identificador = trim((string) ($data['usuario'] ?? $data['correo'] ?? ''));
            $contrasena = (string) ($data['contrasena'] ?? '');

            if ($identificador === '' || $contrasena === '') {
                return $this->jsonResponse($response, [
                    'ok' => false,
                    'mensaje' => 'Debe ingresar usuario o correo y contraseña'
                ], 400);
            }

            $controller = new UsersController();
            $user = $controller->login($identificador, $contrasena);

            return $this->jsonResponse($response, [
                'ok' => true,
                'mensaje' => 'Inicio de sesión exitoso',
                'token' => $user->token,
                'usuario' => $user
            ], 200);
        } catch (Exception $ex) {
            $status = in_array($ex->getCode(), [401, 403], true) ? $ex->getCode() : 400;
            return $this->jsonResponse($response, [
                'ok' => false,
                'mensaje' => $ex->getMessage()
            ], $status);
        }
    }

    public function logout(Request $request, Response $response, $args): Response
    {
        try {
            $data = json_decode($request->getBody()->getContents(), true);
            $token = $this->extractToken($request, is_array($data) ? $data : null);

            if ($token === '') {
                return $this->jsonResponse($response, [
                    'ok' => false,
                    'mensaje' => 'Token requerido'
                ], 401);
            }

            $controller = new UsersController();
            $controller->logout($token);

            return $this->jsonResponse($response, [
                'ok' => true,
                'mensaje' => 'Sesión cerrada correctamente'
            ], 200);
        } catch (Exception $ex) {
            return $this->jsonResponse($response, [
                'ok' => false,
                'mensaje' => $ex->getMessage()
            ], 401);
        }
    }

    public function validarSesion(Request $request, Response $response, $args): Response
    {
        try {
            $token = $this->extractToken($request);

            if ($token === '') {
                return $this->jsonResponse($response, [
                    'ok' => false,
                    'mensaje' => 'Token no proporcionado'
                ], 401);
            }

            $controller = new UsersController();
            $user = $controller->validarSesion($token);

            return $this->jsonResponse($response, [
                'ok' => true,
                'mensaje' => 'Sesión activa',
                'usuario' => $user
            ], 200);
        } catch (Exception $ex) {
            return $this->jsonResponse($response, [
                'ok' => false,
                'mensaje' => $ex->getMessage()
            ], 401);
        }
    }
}
