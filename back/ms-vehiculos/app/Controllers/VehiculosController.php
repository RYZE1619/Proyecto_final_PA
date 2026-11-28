<?php

namespace App\Controllers;

use App\Models\Vehiculo;
use Exception;
use Illuminate\Database\Eloquent\Collection;

class VehiculosController
{
    private const ESTADOS_VALIDOS = ['disponible', 'en_ruta', 'mantenimiento', 'inactivo'];

    public function consultar(array $filtros = []): Collection
    {
        $query = Vehiculo::query();

        if (!empty($filtros['placa'])) {
            $query->where('placa', strtoupper(trim($filtros['placa'])));
        }

        if (!empty($filtros['estado'])) {
            $query->where('estado', $filtros['estado']);
        }

        $tipo = $filtros['tipo_vehiculo'] ?? $filtros['tipo'] ?? null;
        if (!empty($tipo)) {
            $query->where('tipo_vehiculo', $tipo);
        }

        return $query->get();
    }

    public function obtenerPorId(int $id): Vehiculo
    {
        return Vehiculo::findOrFail($id);
    }

    public function crear(array $data): Vehiculo
    {
        $this->validarCamposObligatorios($data);

        $placa = strtoupper(trim($data['placa']));
        if (Vehiculo::where('placa', $placa)->exists()) {
            throw new Exception('Ya existe un vehículo registrado con esa placa', 400);
        }

        $capacidad = (float) $data['capacidad_carga'];
        if ($capacidad <= 0) {
            throw new Exception('La capacidad de carga debe ser mayor a cero', 400);
        }

        $estado = $data['estado'] ?? 'disponible';
        if (!in_array($estado, self::ESTADOS_VALIDOS, true)) {
            throw new Exception('Estado de vehículo no válido', 400);
        }

        $vehiculo = new Vehiculo();
        $vehiculo->placa = $placa;
        $vehiculo->tipo_vehiculo = trim($data['tipo_vehiculo']);
        $vehiculo->capacidad_carga = $capacidad;
        $vehiculo->modelo = trim((string) $data['modelo']);
        $vehiculo->marca = trim($data['marca']);
        $vehiculo->estado = $estado;
        $vehiculo->save();

        return $vehiculo;
    }

    public function editar(int $id, array $data): Vehiculo
    {
        $vehiculo = Vehiculo::findOrFail($id);

        if (isset($data['placa'])) {
            $placa = strtoupper(trim($data['placa']));
            if (Vehiculo::where('placa', $placa)->where('id', '!=', $id)->exists()) {
                throw new Exception('Ya existe otro vehículo con esa placa', 400);
            }
            $vehiculo->placa = $placa;
        }

        if (isset($data['capacidad_carga'])) {
            $capacidad = (float) $data['capacidad_carga'];
            if ($capacidad <= 0) {
                throw new Exception('La capacidad de carga debe ser mayor a cero', 400);
            }
            $vehiculo->capacidad_carga = $capacidad;
        }

        if (isset($data['estado'])) {
            if (!in_array($data['estado'], self::ESTADOS_VALIDOS, true)) {
                throw new Exception('Estado de vehículo no válido', 400);
            }
            $vehiculo->estado = $data['estado'];
        }

        if (isset($data['tipo_vehiculo'])) {
            $vehiculo->tipo_vehiculo = trim($data['tipo_vehiculo']);
        }
        if (isset($data['modelo'])) {
            $vehiculo->modelo = trim((string) $data['modelo']);
        }
        if (isset($data['marca'])) {
            $vehiculo->marca = trim($data['marca']);
        }

        $vehiculo->save();
        return $vehiculo;
    }

    private function validarCamposObligatorios(array $data): void
    {
        $requeridos = [
            'placa',
            'tipo_vehiculo',
            'capacidad_carga',
            'modelo',
            'marca'
        ];

        foreach ($requeridos as $campo) {
            if (!isset($data[$campo]) || trim((string) $data[$campo]) === '') {
                throw new Exception("El campo $campo es obligatorio", 400);
            }
        }
    }
}
