<?php

namespace app\Config;

use Exception;
use PDO;
use PDOException;

class DataLoader
{
    private PDO $pdo;
    private string $host = 'localhost';
    private string $db = '';
    private string $user = '';
    private string $password = '';

    public function __construct()
    {
        $this->connect();
    }

    private function connect(): void
    {
        try {
            $this->pdo = new PDO("mysql:host=$this->host;dbname=$this->db", $this->user, $this->password);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            throw new Exception('Connection failed: ' . $e->getMessage());
        }
    }

    public function findAll(string $table): array
    {
        $stmt = $this->pdo->query('SELECT * FROM ' . $table);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function find(string $table, int $id): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM $table WHERE id = :id");
        $stmt->bindValue(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function insert(string $table, array $data): void
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_map(fn ($key) => ":$key", array_keys($data)));

        $stmt = $this->pdo->prepare("INSERT INTO $table ($columns) VALUES ($placeholders)");

        foreach ($data as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }

        $stmt->execute();
    }

    public function update(string $table, int $id, array $data): void
    {
        $set = implode(', ', array_map(fn ($key) => "$key = :$key", array_keys($data)));

        $stmt = $this->pdo->prepare("UPDATE $table SET $set WHERE id = :id");
        $stmt->bindValue(':id', $id);

        foreach ($data as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }

        $stmt->execute();
    }

    public function delete(string $table, int $id): bool
    {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM $table WHERE id = :id");
            $stmt->bindValue(':id', $id);
            $stmt->execute();
            return true;
        } catch (Exception) {
            return false;
        }
    }
}