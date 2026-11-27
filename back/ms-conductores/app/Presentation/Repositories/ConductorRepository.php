<?php

namespace App\Presentation\Repositories;

use App\Controllers\ConductoresController;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ConductorRepository
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
        $controller = new ConductoresController();
        $rows = $controller->consultar($filtros);

        return $this->jsonResponse($response, [
            'ok' => true,
            'data' => $rows
        ], 200);
    }

    public function obtener(Request $request, Response $response, $args): Response
    {
        try {
            $controller = new ConductoresController();
            $row = $controller->obtenerPorId((int) $args['id']);

            return $this->jsonResponse($response, [
                'ok' => true,
                'data' => $row
            ], 200);
        } catch (ModelNotFoundException $ex) {
            return $this->jsonResponse($response, [
                'ok' => false,
                'mensaje' => 'Conductor no encontrado'
            ], 404);
        }
    }

    public function crear(Request $request, Response $response, $args): Response
    {
        try {
            $data = json_decode($request->getBody()->getContents(), true) ?? [];
            $controller = new ConductoresController();
            $row = $controller->crear($data);

            return $this->jsonResponse($response, [
                'ok' => true,
                'mensaje' => 'Conductor creado correctamente',
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
            $controller = new ConductoresController();
            $row = $controller->editar((int) $args['id'], $data);

            return $this->jsonResponse($response, [
                'ok' => true,
                'mensaje' => 'Conductor actualizado correctamente',
                'data' => $row
            ], 200);
        } catch (ModelNotFoundException $ex) {
            return $this->jsonResponse($response, [
                'ok' => false,
                'mensaje' => 'Conductor no encontrado'
            ], 404);
        } catch (Exception $ex) {
            return $this->jsonResponse($response, [
                'ok' => false,
                'mensaje' => $ex->getMessage()
            ], 400);
        }
    }

    public function cambiarEstado(Request $request, Response $response, $args): Response
    {
        try {
            $data = json_decode($request->getBody()->getContents(), true) ?? [];
            $estado = (string) ($data['estado'] ?? '');

            $controller = new ConductoresController();
            $row = $controller->cambiarEstado((int) $args['id'], $estado);

            return $this->jsonResponse($response, [
                'ok' => true,
                'mensaje' => 'Estado del conductor actualizado',
                'data' => $row
            ], 200);
        } catch (ModelNotFoundException $ex) {
            return $this->jsonResponse($response, [
                'ok' => false,
                'mensaje' => 'Conductor no encontrado'
            ], 404);
        } catch (Exception $ex) {
            return $this->jsonResponse($response, [
                'ok' => false,
                'mensaje' => $ex->getMessage()
            ], 400);
        }
    }
}
