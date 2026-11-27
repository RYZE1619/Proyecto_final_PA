<?php

namespace App\Controllers;

use App\Models\Conductor;
use Exception;
use Illuminate\Database\Eloquent\Collection;

class ConductoresController
{
    private const ESTADOS_VALIDOS = ['disponible', 'en_ruta', 'inactivo'];

    public function consultar(array $filtros = []): Collection
    {
        $query = Conductor::query();

        if (!empty($filtros['documento'])) {
            $query->where('documento', $filtros['documento']);
        }

        if (!empty($filtros['numero_licencia'])) {
            $query->where('numero_licencia', $filtros['numero_licencia']);
        }

        if (!empty($filtros['estado'])) {
            $query->where('estado', $filtros['estado']);
        }

        return $query->get();
    }

    public function obtenerPorId(int $id): Conductor
    {
        return Conductor::findOrFail($id);
    }

    public function crear(array $data): Conductor
    {
        $this->validarCamposObligatorios($data);
        $this->validarFechaVencimiento($data['fecha_vencimiento_licencia']);

        if (Conductor::where('documento', $data['documento'])->exists()) {
            throw new Exception('Ya existe un conductor registrado con ese documento', 400);
        }

        if (Conductor::where('numero_licencia', $data['numero_licencia'])->exists()) {
            throw new Exception('Ya existe un conductor registrado con ese número de licencia', 400);
        }

        if (Conductor::where('correo', $data['correo'])->exists()) {
            throw new Exception('Ya existe un conductor registrado con ese correo', 400);
        }

        $estado = $data['estado'] ?? 'disponible';
        if (!in_array($estado, self::ESTADOS_VALIDOS, true)) {
            throw new Exception('Estado de conductor no válido', 400);
        }

        $conductor = new Conductor();
        $conductor->nombres = trim($data['nombres']);
        $conductor->apellidos = trim($data['apellidos']);
        $conductor->documento = trim($data['documento']);
        $conductor->telefono = trim($data['telefono']);
        $conductor->correo = trim($data['correo']);
        $conductor->numero_licencia = trim($data['numero_licencia']);
        $conductor->categoria_licencia = trim($data['categoria_licencia']);
        $conductor->fecha_vencimiento_licencia = $data['fecha_vencimiento_licencia'];
        $conductor->estado = $estado;
        $conductor->save();

        return $conductor;
    }

    public function editar(int $id, array $data): Conductor
    {
        $conductor = Conductor::findOrFail($id);

        if (isset($data['documento'])) {
            if (Conductor::where('documento', $data['documento'])->where('id', '!=', $id)->exists()) {
                throw new Exception('Ya existe otro conductor con ese documento', 400);
            }
            $conductor->documento = trim($data['documento']);
        }

        if (isset($data['numero_licencia'])) {
            if (Conductor::where('numero_licencia', $data['numero_licencia'])->where('id', '!=', $id)->exists()) {
                throw new Exception('Ya existe otro conductor con ese número de licencia', 400);
            }
            $conductor->numero_licencia = trim($data['numero_licencia']);
        }

        if (isset($data['correo'])) {
            if (Conductor::where('correo', $data['correo'])->where('id', '!=', $id)->exists()) {
                throw new Exception('Ya existe otro conductor con ese correo', 400);
            }
            $conductor->correo = trim($data['correo']);
        }

        if (isset($data['fecha_vencimiento_licencia'])) {
            $this->validarFechaVencimiento($data['fecha_vencimiento_licencia']);
            $conductor->fecha_vencimiento_licencia = $data['fecha_vencimiento_licencia'];
        }

        if (isset($data['estado'])) {
            if (!in_array($data['estado'], self::ESTADOS_VALIDOS, true)) {
                throw new Exception('Estado de conductor no válido', 400);
            }
            $conductor->estado = $data['estado'];
        }

        if (isset($data['nombres'])) {
            $conductor->nombres = trim($data['nombres']);
        }
        if (isset($data['apellidos'])) {
            $conductor->apellidos = trim($data['apellidos']);
        }
        if (isset($data['telefono'])) {
            $conductor->telefono = trim($data['telefono']);
        }
        if (isset($data['categoria_licencia'])) {
            $conductor->categoria_licencia = trim($data['categoria_licencia']);
        }

        $conductor->save();
        return $conductor;
    }

    public function cambiarEstado(int $id, string $estado): Conductor
    {
        if (!in_array($estado, self::ESTADOS_VALIDOS, true)) {
            throw new Exception('Estado no válido. Valores permitidos: disponible, en_ruta, inactivo', 400);
        }

        $conductor = Conductor::findOrFail($id);
        $conductor->estado = $estado;
        $conductor->save();

        return $conductor;
    }

    private function validarCamposObligatorios(array $data): void
    {
        $requeridos = [
            'nombres',
            'apellidos',
            'documento',
            'telefono',
            'correo',
            'numero_licencia',
            'categoria_licencia',
            'fecha_vencimiento_licencia'
        ];

        foreach ($requeridos as $campo) {
            if (empty($data[$campo]) || trim((string) $data[$campo]) === '') {
                throw new Exception("El campo $campo es obligatorio", 400);
            }
        }
    }

    private function validarFechaVencimiento(string $fecha): void
    {
        $timestamp = strtotime($fecha);
        if ($timestamp === false) {
            throw new Exception('La fecha de vencimiento de licencia no es válida', 400);
        }

        if ($fecha < date('Y-m-d')) {
            throw new Exception('La licencia se encuentra vencida', 400);
        }
    }
}
