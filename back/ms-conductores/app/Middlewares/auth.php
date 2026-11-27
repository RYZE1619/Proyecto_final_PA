<?php

use Illuminate\Database\Capsule\Manager as Capsule;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Psr7\Response;

return function (Request $request, $handler) {
    if ($request->getMethod() === 'OPTIONS') {
        return $handler->handle($request);
    }

    $authHeader = $request->getHeaderLine('Authorization');
    $token = '';
    if (str_starts_with($authHeader, 'Bearer ')) {
        $token = trim(substr($authHeader, 7));
    } elseif (!empty($authHeader)) {
        $token = trim($authHeader);
    }

    if ($token === '') {
        $response = new Response();
        $response->getBody()->write(json_encode([
            'ok' => false,
            'mensaje' => 'Token de autenticación requerido'
        ]));
        return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
    }

    $usuario = Capsule::connection('auth')
        ->table('usuarios')
        ->where('token', $token)
        ->where('sesion_activa', true)
        ->where('estado', 'activo')
        ->first();

    if (!$usuario) {
        $response = new Response();
        $response->getBody()->write(json_encode([
            'ok' => false,
            'mensaje' => 'Sesión inválida o expirada'
        ]));
        return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
    }

    return $handler->handle($request);
};
