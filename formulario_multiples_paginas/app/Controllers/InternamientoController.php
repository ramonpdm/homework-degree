<?php

namespace App\Controllers;

class InternamientoController extends Controller
{
    public function handle(): string
    {
        $method = $_GET['method'] ?? null;
        $requestMethod = $_SERVER['REQUEST_METHOD'];

        return match ($requestMethod) {
            'GET' => $this->getInternamientos(),
            'POST' => match ($method) {
                'update' => $this->updateInternamiento(),
                'delete' => $this->deleteInternamiento(),
                default => $this->addInternamiento(),
            },
            default => json_encode(['error' => 'Método no soportado']),
        };
    }

    private function getData(): array
    {
        return $this->dataLoader->findAll()['internamientos'] ?? [];
    }

    private function getInternamientos(): string
    {
        $id = $_GET['id'] ?? null;
        $personaId = $_GET['persona_id'] ?? null;
        $data = $this->getData();

        if ($id) {
            $internamiento = array_filter($data, fn ($i) => $i['id'] == $id);
            return json_encode(reset($internamiento) ?: []);
        } elseif ($personaId) {
            $internamientos = array_filter($data, fn ($i) => $i['persona_id'] == $personaId);
            return json_encode($internamientos);
        }

        return json_encode(['error' => 'ID no proporcionado']);
    }

    private function addInternamiento(): string
    {
        $payload = $this->getPayload();

        if (isset($payload['persona_id'], $payload['fecha'], $payload['centro_medico'], $payload['diagnostico'])) {
            $data = $this->getData();
            $newId = array_reduce($data, fn ($c, $i) => max($i['id'], $c), 0) + 1;
            $internamiento = [
                'id' => $newId,
                'persona_id' => $payload['persona_id'],
                'fecha' => $payload['fecha'],
                'centro_medico' => $payload['centro_medico'],
                'diagnostico' => $payload['diagnostico']
            ];
            $data[] = $internamiento;
            $this->dataLoader->saveData($data, 'internamientos');
            return json_encode(['message' => 'success']);
        }

        return json_encode(['error' => 'Error al guardar el internamiento']);
    }

    private function updateInternamiento(): string
    {
        $payload = $this->getPayload();
        $id = $payload['id'] ?? null;

        if ($id && isset($payload['fecha'], $payload['centro_medico'], $payload['diagnostico'])) {
            $data = $this->getData();
            $index = array_search($id, array_column($data, 'id'));
            if ($index !== false) {
                $data[$index] = [
                    'id' => $id,
                    'persona_id' => $payload['persona_id'],
                    'fecha' => $payload['fecha'],
                    'centro_medico' => $payload['centro_medico'],
                    'diagnostico' => $payload['diagnostico']
                ];
                $this->dataLoader->saveData($data, 'internamientos');
                return json_encode(['message' => 'success']);
            }
        }

        return json_encode(['error' => 'Datos incompletos']);
    }

    private function deleteInternamiento(): string
    {
        $id = $_GET['id'] ?? null;

        if ($id) {
            $data = $this->getData();
            $index = array_search($id, array_column($data, 'id'));
            if ($index !== false) {
                unset($data[$index]);
                $this->dataLoader->saveData($data, 'internamientos');
                return json_encode(['message' => 'success']);
            }
        }

        return json_encode(['error' => 'Internamiento no encontrado']);
    }
}