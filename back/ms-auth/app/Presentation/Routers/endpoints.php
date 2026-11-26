<?php

use App\Presentation\Repositories\UserRepository;
use Slim\App;

return function (App $app) {
    $app->post('/login', [UserRepository::class, 'login']);
    $app->post('/logout', [UserRepository::class, 'logout']);
    $app->get('/validar-sesion', [UserRepository::class, 'validarSesion']);
};
