<?php

namespace Models;

use \DTOs\DTO;
use Exception;

use function PHPUnit\Framework\throwException;

class Task extends Model {

    /** @var int */
    public ?int $id = 0;

    /** @var string */
    public string $title = '';

    /** @var string|null */
    public ?string $description = '';

    /** @var 'pending'|'completed' */
    public string $status = 'pending';

    /** @var string|null */
    public ?string $due_date = '';

    /** @var string|null */
    public ?string $created_at = '';

    /** @var string|null */
    public ?string $updated_at = '';

    protected function table(): string
    {
        return 'tasks';
    }

    protected function fillable(): array
    {
        return [
            'title',
            'description',
            'status',
            'due_date',
        ];
    }

    /**
     * Cria uma nova tarefa.
     *
     * @param object $data Dados da tarefa (ex: ['title' => 'Minha Tarefa'])
     * @return self A instância persistida ou Exception em caso de falha.
     */
    public function create(DTO $dto): self
    {
        if ($this->id != 0)
            throw new Exception('Task already exists');

        // Garante que apenas os campos esperados sejam inseridos
        $this->fromObject($dto);

        $sql = "INSERT INTO tasks (title, description, due_date, status) VALUES (:title, :description, :due_date, :status)";

        $stmt = $this->prepare($sql);

        $stmt->execute([
            ':title' => $this->title,
            ':description' => $this->description,
            ':due_date' => $this->due_date,
            ':status' => $this->status
        ]);

        $id = $this->lastInsertId();

        if (!$id) {
            return $this->throwPDOError();
        }

        // Busca os dados recém-criados para obter campos automáticos como timestamps
        $this->find($id);
        return $this;
    }

    /**
     * Atualiza uma tarefa existente.
     *
     * @param int $id
     * @param object $data
     * @return bool
     */
    public function update(DTO $dto): bool
    {
        if (!$this->id) {
            throw new Exception('Task ID is required for update.');
        }

        $fields = [];
        $params[":id"] = $this->id;

        $data = $dto->toArray();
        unset($data['id']); // Remove o ID

        foreach($data as $k => $v) {
            if (property_exists($this, $k) && $this->$k !== $v) {
                $fields[] = "{$k} = :{$k}";
                $params[":{$k}"] = $v;
            }
        }

        if (empty($fields)) {
            return true; // Nothing to update, consider it a successful "update"
        }

        $sql = "UPDATE tasks SET " . implode(', ', $fields) . " WHERE id = :id";

        $stmt = $this->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Exclui uma tarefa pelo ID.
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        $stmt = $this->prepare("DELETE FROM tasks WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }
}
