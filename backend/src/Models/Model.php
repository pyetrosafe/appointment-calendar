<?php

namespace Models;

use Services\Database;
use \PDO;
use Exception;
use PDOException;
use Symfony\Component\VarDumper\Cloner\Data;
use Throwable;

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
        $errorInfo = $this->getConnection()->errorInfo();

        // '00000' means no error
        if (!empty($errorInfo[0]) && $errorInfo[0] !== '00000') {
            $errorMessage = "SQLSTATE: " . $errorInfo[0] . " - Driver Error Code: " . $errorInfo[1] . " - Error Message: " . $errorInfo[2];
            throw new Exception('Failed to execute query: ' . $errorMessage);
        } else {
            // If no specific PDO error, throw a generic exception
            throw new Exception('Failed to execute query: Unknown PDO error.');
        }
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

    /**
     * Preenche o model com um array de atributos.
     * Apenas os atributos definidos na propriedade $fillable do model filho serão preenchidos.
     *
     * @param array $attributes
     * @return static
     */
    public function fill(array $attributes): static
    {
        // Chama o método fillable() se ele existir no model filho.
        if (!method_exists($this, 'fillable')) {
            return $this;
        }

        foreach ($this->fillable() as $key) {
            if (array_key_exists($key, $attributes)) {
                $this->{$key} = $attributes[$key];
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

    /**
     * Handle dynamic static method calls.
     */
    public static function __callStatic(string $name, array $arguments)
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
     * @return static|false
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
     * Salva o modelo no banco de dados, decidindo entre Inserir ou Atualizar.
     *
     * @return bool
     */
    public function save(): bool
    {
        try {
            if (isset($this->id) && $this->id > 0) {
                return $this->performUpdate();
            } else {
                return $this->performInsert();
            }
        } catch (PDOException $e) {
            $this->throwPDOError();
        } catch (Throwable $e) {
            throw $e;
        }
    }

    /**
     * Cria e salva uma nova instância do modelo.
     *
     * @param array $attributes Os atributos para preencher o modelo.
     * @return static A nova instância do modelo.
     */
    protected function create(array $values): static
    {
        try {
            $instance = (new static())->fill($values);
            $instance->save();
            return $instance;
        } catch (PDOException $e) {
            $this->throwPDOError();
        } catch (Throwable $e) {
            throw $e;
        }
    }

    /**
     * Exclui o registro do modelo do banco de dados.
     *
     * @return bool
     */
    public function delete(): bool
    {
        if (!isset($this->id) || $this->id <= 0) {
            return false;
        }

        try {
            $sql = "DELETE FROM " . $this->table() . " WHERE id = :id";
            $stmt = $this->getConnection()->prepare($sql);
            $success = $stmt->execute([':id' => $this->id]);

            if (!$success && $stmt->errorCode() !== '00000') {
                $this->throwPDOError();
            }

            return $success;
        } catch (PDOException $e) {
            $this->throwPDOError();
        } catch (Throwable $e) {
            throw $e;
        }
    }

    /**
     * Exclui um ou mais modelos pelo seu ID.
     *
     * @param int|array $ids
     * @return int O número de registros excluídos.
     */
    protected function destroy(int|array $ids): int
    {
        if (is_int($ids)) {
            $ids = [$ids];
        }

        if (empty($ids)) {
            return 0;
        }

        try {
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $sql = "DELETE FROM " . $this->table() . " WHERE id IN ({$placeholders})";

            $stmt = $this->getConnection()->prepare($sql);
            $stmt->execute($ids);

            return $stmt->rowCount();
        } catch (PDOException $e) {
            $this->throwPDOError();
        } catch (Throwable $e) {
            throw $e;
        }
    }

    /**
     * Executa a lógica de UPDATE para um modelo existente.
     *
     * @return bool
     */
    protected function performUpdate(): bool
    {
        try {
            $params = $this->getProperties();

            if (empty($params)) {
                return false; // Nada a ser atualizado
            }

            $values = $this->getParamsValues($params);
            $values[':id'] = $this->id;

            $sql = "UPDATE " . $this->table() . " SET " . implode(', ', array_column($params, 'key_marker')) . " WHERE id = :id";

            $stmt = $this->prepare($sql);
            $success = $stmt->execute($values);

            if (!$success && $stmt->errorCode() !== '00000') {
                $this->throwPDOError();
            }

            return $success;
        } catch (PDOException $e) {
            $this->throwPDOError();
        } catch (Throwable $e) {
            throw $e;
        }
    }

    /**
     * Executa a lógica de INSERT para um novo modelo.
     *
     * @return bool
     */
    protected function performInsert(): bool
    {
        try {
            $params = $this->getProperties();

            if (empty($params)) {
                return false; // Nada a ser inserido
            }

            $columns = implode(', ', array_keys($params));
            $markers = implode(', ', array_column($params, 'marker'));

            $sql = "INSERT INTO " . $this->table() . " ({$columns}) VALUES ({$markers})";

            $stmt = $this->getConnection()->prepare($sql);
            $success = $stmt->execute($this->getParamsValues($params));

            if ($success) {
                $this->id = $this->getConnection()->lastInsertId();
                // Reload the model to get auto-generated fields like 'id', 'created_at', 'updated_at'
                $this->find($this->id);
            }
            else if ($stmt->errorCode() !== '00000') {
                $this->throwPDOError();
            }

            return $success;
        } catch (PDOException $e) {
            $this->throwPDOError();
        } catch (Throwable $e) {
            throw $e;
        }
    }

    /**
     * Retorna um array associativo dos atributos do modelo para inserção ou atualização.
     * Prioriza o método fillable() se definido, caso contrário, usa todas as propriedades do objeto.
     *
     * @return array
     */
    protected function getProperties(): array
    {
        $fields = method_exists($this, 'fillable') ? $this->fromFillable() : $this->fromObjectVars();

        $params = [];
        foreach($fields as $key => $value) {
            $params[$key] = array(
                'marker' => ":{$key}",
                'key_marker' => "{$key} = :{$key}",
                'value' => $value
            );
        }

        return $params;
    }

    /**
     * Retorna um array associativo dos atributos do modelo que são definidos no método fillable().
     *
     * @return array
     */
    protected function fromFillable(): array
    {
        $params = [];
        foreach ($this->fillable() as $key) {
             if (property_exists($this, $key)) {
                $params[$key] = $this->{$key};
            }
        }
        return $params;
    }

    /**
     * Retorna um array associativo de todas as propriedades públicas e protegidas do modelo,
     * excluindo 'id', 'created_at', 'updated_at' e 'deleted_at'.
     *
     * @return array
     */
    protected function fromObjectVars(): array
    {
        $params = get_object_vars($this);
        unset($params['id']); // Remove o ID para inserção
        unset($params['created_at']); // Remove created_at para inserção
        unset($params['updated_at']); // Remove updated_at para inserção
        unset($params['deleted_at']); // Remove deleted_at para inserção

        return $params;
    }

    private function getParamsValues(array $params): array
    {
        return array_combine(array_column($params, 'marker'), array_column($params, 'value'));
    }
}
