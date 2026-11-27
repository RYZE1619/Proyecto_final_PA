<?php

use App\Presentation\Repositories\ConductorRepository;
use Slim\App;

return function (App $app) {
    $app->get('/conductores', [ConductorRepository::class, 'consultar']);
    $app->get('/conductores/{id}', [ConductorRepository::class, 'obtener']);
    $app->post('/conductores', [ConductorRepository::class, 'crear']);
    $app->put('/conductores/{id}', [ConductorRepository::class, 'editar']);
    $app->patch('/conductores/{id}/estado', [ConductorRepository::class, 'cambiarEstado']);
};
