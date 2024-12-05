<?php

namespace App\Controllers;

class EnfermedadController extends Controller
{
    public function handle(): string
    {
        $method = $_GET['method'] ?? null;
        $requestMethod = $_SERVER['REQUEST_METHOD'];

        return match ($requestMethod) {
            'GET' => $this->getEnfermedades(),
            'POST' => match ($method) {
                'update' => $this->updateEnfermedad(),
                'delete' => $this->deleteEnfermedad(),
                default => $this->addEnfermedad(),
            },
            default => json_encode(['error' => 'Método no soportado']),
        };
    }

    private function getData(): array
    {
        return $this->dataLoader->findAll()['enfermedades'] ?? [];
    }

    private function getEnfermedades(): string
    {
        $id = $_GET['id'] ?? null;
        $personaId = $_GET['persona_id'] ?? null;
        $data = $this->getData();

        if ($id) {
            $enfermedad = array_filter($data, fn ($e) => $e['id'] == $id);
            return json_encode(reset($enfermedad) ?: []);
        } elseif ($personaId) {
            $enfermedades = array_filter($data, fn ($e) => $e['persona_id'] == $personaId);
            return json_encode(array_values($enfermedades));
        }

        return json_encode(['error' => 'ID no proporcionado']);
    }

    private function addEnfermedad(): string
    {
        $payload = $this->getPayload();

        if (isset($payload['persona_id'], $payload['enfermedad'], $payload['tiempo'], $payload['detalles'])) {
            $data = $this->getData();
            $newId = array_reduce($data, fn ($c, $e) => max($e['id'], $c), 0) + 1;
            $enfermedad = [
                'id' => $newId,
                'persona_id' => $payload['persona_id'],
                'nombre' => $payload['enfermedad'],
                'tiempo' => $payload['tiempo'],
                'detalles' => $payload['detalles']
            ];
            $data[] = $enfermedad;
            $this->dataLoader->saveData($data, 'enfermedades');
            return json_encode(['message' => 'success']);
        }

        return json_encode(['error' => 'Error al guardar la enfermedad']);
    }

    private function updateEnfermedad(): string
    {
        $payload = $this->getPayload();
        $id = $payload['id'] ?? null;

        if ($id && isset($payload['enfermedad'], $payload['tiempo'], $payload['detalles'])) {
            $data = $this->getData();
            $index = array_search($id, array_column($data, 'id'));
            if ($index !== false) {
                $data[$index] = [
                    'id' => $id,
                    'persona_id' => $payload['persona_id'],
                    'nombre' => $payload['enfermedad'],
                    'tiempo' => $payload['tiempo'],
                    'detalles' => $payload['detalles']
                ];
                $this->dataLoader->saveData($data, 'enfermedades');
                return json_encode(['message' => 'success']);
            }
        }

        return json_encode(['error' => 'Datos incompletos']);
    }

    private function deleteEnfermedad(): string
    {
        $id = $_GET['id'] ?? null;

        if ($id) {
            $data = $this->getData();
            $index = array_search($id, array_column($data, 'id'));
            if ($index !== false) {
                unset($data[$index]);
                $this->dataLoader->saveData($data, 'enfermedades');
                return json_encode(['message' => 'success']);
            }
        }

        return json_encode(['error' => 'Enfermedad no encontrada']);
    }
}