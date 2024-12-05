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
        return $this->dataLoader->findAll()['personas'] ?? [];
    }

    public function findAll(): array
    {
        return $this->getData();
    }

    public function find($id): ?array
    {
        $filtered = array_filter($this->getData(), fn ($persona) => $persona['id'] == $id);
        return reset($filtered) ?: null;
    }

    public function insert($person): array
    {
        $personId = $person['id'] ?? null;

        if ($personId) {
            unset($person['id']);
            return $this->insertFamiliar($personId, $person);
        }

        return $this->insertPerson($person);
    }

    private function insertPerson($person): array
    {
        $this->validatePersonPayload($person);
        $data = $this->getData();
        $newId = array_reduce($data, fn ($c, $persona) => max($persona['id'], $c), 0) + 1;
        $person['id'] = $newId;
        $data[] = $person;
        $this->dataLoader->saveData($data, 'personas');
        return $person;
    }

    private function insertFamiliar($id, $familiar): ?array
    {
        $this->validateFamiliarPayload($familiar);
        $data = $this->getData();
        $index = array_search($id, array_column($data, 'id'));

        if ($index !== false) {
            $familiares = $data[$index]['familiares'] ?? [];
            $familiar['id'] = $familiar['familiar_id'];
            unset($familiar['familiar_id']);
            $familiares[] = $familiar;
            $data[$index]['familiares'] = $familiares;
            $this->dataLoader->saveData($data, 'personas');
            return $familiar;
        }

        return null;
    }

    public function update($id, $person): ?array
    {
        $this->validatePersonPayload($person);
        $data = $this->getData();
        $index = array_search($id, array_column($data, 'id'));

        if ($index !== false) {
            unset($person['familiares']);
            $data[$index] = $person + $data[$index];
            $this->dataLoader->saveData($data, 'personas');
            return $person;
        }

        return null;
    }

    public function delete($id, $familiarId = null): bool
    {
        if ($familiarId) {
            return $this->deleteFamiliar($id, $familiarId);
        }

        return $this->deletePerson($id);
    }

    private function deletePerson($id): bool
    {
        $data = $this->getData();
        $index = array_search($id, array_column($data, 'id'));

        if ($index !== false) {
            array_splice($data, $index, 1);
            $this->dataLoader->saveData($data, 'personas');
            return true;
        }

        return false;
    }

    private function deleteFamiliar($id, $familiarId): bool
    {
        $data = $this->getData();
        $index = array_search($id, array_column($data, 'id'));

        if ($index !== false) {
            $familiares = $data[$index]['familiares'] ?? [];
            $familiarIndex = array_search($familiarId, array_column($familiares, 'id'));

            if ($familiarIndex !== false) {
                array_splice($familiares, $familiarIndex, 1);
                $data[$index]['familiares'] = $familiares;
                $this->dataLoader->saveData($data, 'personas');
                return true;
            }
        }

        return false;
    }

    private function validatePersonPayload(&$person): void
    {
        !is_array($person) && throw new Exception('Payload inválido');
        !isset($person['nombre']) && throw new Exception('Nombre es requerido');
        !isset($person['edad']) && throw new Exception('Edad es requerida');
        !is_string($person['nombre']) && throw new Exception('Nombre inválido');
        !is_numeric($person['edad']) && throw new Exception('Edad inválida');
        $person['edad'] < 0 && throw new Exception('Edad debe ser un número positivo');
        $person['familiares'] = $person['familiares'] ?? [];

        !is_array($person['familiares']) && throw new Exception('Familiares inválidos');
        foreach ($person['familiares'] as $familiar) {
            $this->validateFamiliarPayload($familiar);
        }

        // Limpiar los campos
        isset($person['id']) && $person['id'] = (int)$person['id'];
        $person['edad'] = (int)$person['edad'];
        $person['nombre'] = trim(htmlspecialchars($person['nombre']));
    }

    private function validateFamiliarPayload(&$familiar): void
    {
        !isset($familiar['id']) && !isset($familiar['familiar_id']) && throw new Exception('ID es requerido');
        !is_array($familiar) && throw new Exception('Payload inválido');
        !isset($familiar['parentesco']) && throw new Exception('Parentesco es requerido');
        !is_string($familiar['parentesco']) && throw new Exception('Parentesco inválido');

        isset($familiar['id']) && $familiar['id'] = (int)$familiar['id'];
        isset($familiar['familiar_id']) && $familiar['familiar_id'] = (int)$familiar['familiar_id'];
        $familiar['parentesco'] = trim(htmlspecialchars($familiar['parentesco']));
    }
}
