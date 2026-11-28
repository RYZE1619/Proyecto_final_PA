<?php

namespace App\Presentation\Repositories;

use App\Controllers\VehiculosController;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class VehiculoRepository
{
    private function jsonResponse(Response $response, mixed $data, int $status = 200): Response
    {
        $response->getBody()->write(json_encode($data));
        return $response
            ->withStatus($status)
            ->withHeader('Content-Type', 'application/json');
    }

    public function consultar(Request $request, Response $response, $args): Response
    {
        $filtros = $request->getQueryParams();
        $controller = new VehiculosController();
        $rows = $controller->consultar($filtros);

        return $this->jsonResponse($response, [
            'ok' => true,
            'data' => $rows
        ], 200);
    }

    public function obtener(Request $request, Response $response, $args): Response
    {
        try {
            $controller = new VehiculosController();
            $row = $controller->obtenerPorId((int) $args['id']);

            return $this->jsonResponse($response, [
                'ok' => true,
                'data' => $row
            ], 200);
        } catch (ModelNotFoundException $ex) {
            return $this->jsonResponse($response, [
                'ok' => false,
                'mensaje' => 'Vehículo no encontrado'
            ], 404);
        }
    }

    public function crear(Request $request, Response $response, $args): Response
    {
        try {
            $data = json_decode($request->getBody()->getContents(), true) ?? [];
            $controller = new VehiculosController();
            $row = $controller->crear($data);

            return $this->jsonResponse($response, [
                'ok' => true,
                'mensaje' => 'Vehículo creado correctamente',
                'data' => $row
            ], 201);
        } catch (Exception $ex) {
            return $this->jsonResponse($response, [
                'ok' => false,
                'mensaje' => $ex->getMessage()
            ], 400);
        }
    }

    public function editar(Request $request, Response $response, $args): Response
    {
        try {
            $data = json_decode($request->getBody()->getContents(), true) ?? [];
            $controller = new VehiculosController();
            $row = $controller->editar((int) $args['id'], $data);

            return $this->jsonResponse($response, [
                'ok' => true,
                'mensaje' => 'Vehículo actualizado correctamente',
                'data' => $row
            ], 200);
        } catch (ModelNotFoundException $ex) {
            return $this->jsonResponse($response, [
                'ok' => false,
                'mensaje' => 'Vehículo no encontrado'
            ], 404);
        } catch (Exception $ex) {
            return $this->jsonResponse($response, [
                'ok' => false,
                'mensaje' => $ex->getMessage()
            ], 400);
        }
    }
}
