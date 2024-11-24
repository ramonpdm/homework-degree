<?php

namespace App\Controllers;

use Exception;

class PersonController extends Controller
{
    public function handle(): string
    {
        $id = $_GET['id'] ?? null;
        $payload = $this->getPayload();
        $method = $_GET['method'] ?? null;
        $requestMethod = $_SERVER['REQUEST_METHOD'];

        switch ($requestMethod) {
            case 'GET':
                if (!$id) {
                    return $this->sendOutput($this->findAll());
                }

                if ($persona = $this->find($id)) {
                    return $this->sendOutput($persona);
                }

                return $this->sendOutput(['error' => 'Persona no encontrada'], 404);

            case 'POST':
                switch ($method) {
                    case 'update':
                        $this->update($id, $payload);
                        return $this->sendOutput(['message' => 'success']);

                    case 'delete':
                        $familiarId = $_GET['familiar_id'] ?? null;
                        $result = $this->delete($id, $familiarId);
                        if (!$result) {
                            return $this->sendOutput(['error' => 'Persona no encontrada'], 404);
                        }
                        return $this->sendOutput(['message' => 'success']);

                    default:
                        $persona = $this->insert($payload);
                        return $this->sendOutput($persona);
                }
            default:
                return $this->sendOutput(['error' => 'Método no permitido'], 405);
        }
    }

    private function getData(): array
    {
        return $this->dataLoader->findAll('personas');
    }

    public function findAll(): array
    {
        return $this->getData();
    }

    public function find($id): ?array
    {
        $person = $this->dataLoader->find('personas', $id);
        return $person ? $person : null;
    }

    public function insert($person): array
    {
        $this->validatePersonPayload($person);
        $this->dataLoader->insert('personas', $person);
        return $person;
    }

    public function update($id, $person): ?array
    {
        $this->validatePersonPayload($person);
        $this->dataLoader->update('personas', $id, $person);
        return $person;
    }

    public function delete($id): bool
    {
        return $this->dataLoader->delete('personas', $id);
    }

    private function validatePersonPayload(&$person): void
    {
        !is_array($person) && throw new Exception('Payload inválido');
        !isset($person['nombre']) && throw new Exception('Nombre es requerido');
        !isset($person['edad']) && throw new Exception('Edad es requerida');
        !is_string($person['nombre']) && throw new Exception('Nombre inválido');
        !is_numeric($person['edad']) && throw new Exception('Edad inválida');
        $person['edad'] < 0 && throw new Exception('Edad debe ser un número positivo');

        // Limpiar los campos
        isset($person['id']) && $person['id'] = (int)$person['id'];
        $person['edad'] = (int)$person['edad'];
        $person['nombre'] = trim(htmlspecialchars($person['nombre']));
    }
}
