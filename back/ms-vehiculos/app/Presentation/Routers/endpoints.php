<?php

use App\Presentation\Repositories\VehiculoRepository;
use Slim\App;

return function (App $app) {
    $app->get('/vehiculos', [VehiculoRepository::class, 'consultar']);
    $app->get('/vehiculos/{id}', [VehiculoRepository::class, 'obtener']);
    $app->post('/vehiculos', [VehiculoRepository::class, 'crear']);
    $app->put('/vehiculos/{id}', [VehiculoRepository::class, 'editar']);
};
