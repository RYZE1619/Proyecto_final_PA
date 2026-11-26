<?php

namespace App\Controllers;

use App\Models\User;
use Exception;

class UsersController
{
    public function login(string $identificador, string $contrasena): User
    {
        $user = User::where(function ($query) use ($identificador) {
            $query->where('usuario', $identificador)
                  ->orWhere('correo', $identificador);
        })->first();

        if (!$user || $user->contrasena !== $contrasena) {
            throw new Exception('Credenciales incorrectas', 401);
        }

        if ($user->estado !== 'activo') {
            throw new Exception('El usuario se encuentra inactivo', 403);
        }

        $user->token = bin2hex(random_bytes(32));
        $user->sesion_activa = true;
        $user->save();

        return $user;
    }

    public function logout(string $token): void
    {
        $user = User::where('token', $token)
            ->where('sesion_activa', true)
            ->first();

        if (!$user) {
            throw new Exception('Sesión no válida o ya cerrada', 401);
        }

        $user->token = null;
        $user->sesion_activa = false;
        $user->save();
    }

    public function validarSesion(string $token): User
    {
        $user = User::where('token', $token)
            ->where('sesion_activa', true)
            ->where('estado', 'activo')
            ->first();

        if (!$user) {
            throw new Exception('Token inválido o sesión inactiva', 401);
        }

        return $user;
    }
}
