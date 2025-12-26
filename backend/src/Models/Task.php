<?php

namespace Models;

class Task extends Model {

    /**
     * Busca todas as tarefas do banco de dados.
     *
     * @return array
     */
    public function get(): array
    {
        $stmt = $this->pdo->query("SELECT * FROM tasks ORDER BY created_at DESC");
        return $stmt->fetchAll();
    }

    /**
     * Busca uma única tarefa pelo seu ID.
     *
     * @param int $id
     * @return array|false
     */
    public function find(int $id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM tasks WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Cria uma nova tarefa.
     *
     * @param array $data Dados da tarefa (ex: ['title' => 'Minha Tarefa'])
     * @return string|false O ID da última linha inserida ou falso em caso de falha.
     */
    public function create(array $data)
    {
        // Garante que apenas os campos esperados sejam inseridos
        $title = $data['title'] ?? 'Nova Tarefa';
        $description = $data['description'] ?? null;
        $dueDate = $data['due_date'] ?? null;

        $sql = "INSERT INTO tasks (title, description, due_date) VALUES (:title, :description, :due_date)";

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            ':title' => $title,
            ':description' => $description,
            ':due_date' => $dueDate
        ]);

        return $this->pdo->lastInsertId();
    }

    /**
     * Atualiza uma tarefa existente.
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data): bool
    {
        $fields = [];
        $params = [];

        if (isset($data['title'])) {
            $fields[] = 'title = :title';
            $params[':title'] = $data['title'];
        }
        if (array_key_exists('description', $data)) {
            $fields[] = 'description = :description';
            $params[':description'] = $data['description'];
        }
        if (array_key_exists('due_date', $data)) {
            $fields[] = 'due_date = :due_date';
            $params[':due_date'] = $data['due_date'];
        }

        if (empty($fields)) {
            return false;
        }

        $sql = "UPDATE tasks SET " . implode(', ', $fields) . " WHERE id = :id";
        $params[':id'] = $id;

        $stmt = $this->pdo->prepare($sql);
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
        $stmt = $this->pdo->prepare("DELETE FROM tasks WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }
}
