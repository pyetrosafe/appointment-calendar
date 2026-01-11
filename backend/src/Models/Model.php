<?php

namespace Models;

use Services\Database;
use \PDO;
use Exception;
use Symfony\Component\VarDumper\Cloner\Data;

abstract class Model {

    public function __construct()
    {}

    protected function db(): Database
    {
        return Database::getInstance();
    }

    protected function getConnection(): PDO
    {
        return $this->db()->getConnection();
    }

    protected function throwPDOError()
    {
        throw new Exception('Failed to execute query:' . $this->getConnection()->errorCode() . ' - ' . implode(" ", $this->pdo->errorInfo()));
    }

    protected function fromObject($object)
    {
        // Garante que apenas os campos esperados sejam inseridos
        foreach ($object as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }

        return $this;
    }

    public function __call($name, $arguments)
    {
        if ($this::class !== self::class && method_exists($this, $name)) {
            return call_user_func_array([$this, $name], $arguments);
        }
        if (method_exists(static::class, $name)) {
            return call_user_func_array([static::class, $name], $arguments);
        }
        else if (method_exists(Database::class, $name)) {
            $dbInstance = Database::getInstance();
            return call_user_func_array([$dbInstance, $name], $arguments);
        }
        else {
            throw new \Exception("Method {$name} does not exist.");
        }
    }

    public static function __callStatic($name, $arguments)
    {
        if (method_exists(static::class, $name)) {
            return call_user_func_array([new static(), $name], $arguments);
        }
        // else if (method_exists(self::class, $name)) {
        //     return call_user_func_array([new self(), $name], $arguments);
        // }
        else {
            throw new \Exception("Method {$name} does not exist.");
        }
    }

    abstract protected function table(): string;

    /**
     * Busca todas as tarefas do banco de dados.
     *
     * @return array
     */
    protected function all(): array
    {
        $stmt = $this->query("SELECT * FROM " . $this->table() . " ORDER BY created_at DESC");
        $results = $stmt->fetchAll(PDO::FETCH_OBJ);

        $collection = [];
        foreach ($results as $result) {
            $model = new static();
            $model->fromObject($result);
            $collection[] = $model;
        }
        
        return $collection;
    }

    /**
     * Busca uma única tarefa pelo seu ID.
     *
     * @param int $id
     * @return array|false
     */
    protected function find(int $id): ?static
    {
        $stmt = $this->prepare("SELECT * FROM " . $this->table() . " WHERE id = ?");
        $stmt->execute([$id]);
        $data = $stmt->fetch(PDO::FETCH_OBJ);

        if (!$data) {
            return null;
        }
        
        $this->fromObject($data);
        return $this;
    }

    /**
     * Salva o modelo no banco de dados.
     *
     * @return bool
     */
    protected function save(): bool
    {
        $fields = get_object_vars($this);
        unset($fields['id']); // Remove o ID para inserção
        unset($fields['created_at']); // Remove created_at para inserção

        $columns = implode(', ', array_keys($fields));
        $placeholders = implode(', ', array_fill(0, count($fields), '?'));

        $stmt = $this->prepare("INSERT INTO " . $this->table() . " ({$columns}) VALUES ({$placeholders})");
        return $stmt->execute(array_values($fields));
    }
}
