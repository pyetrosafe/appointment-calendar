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
}
