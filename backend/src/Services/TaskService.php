<?php

namespace Services;

use DTOs\TaskDTO;
use Models\Task;
use Respect\Validation\Validator as v;
use Respect\Validation\Exceptions\NestedValidationException;

class TaskService
{
    /**
     * @return TaskDTO[]
     */
    public function getAllTasks(): array
    {
        $tasks = Task::all();

        return array_map(function (Task $task) {
            return TaskDTO::fromObject($task);
        }, $tasks);
    }

    public function getTask(int $id): ?TaskDTO
    {
        $task = Task::find($id);

        if (!$task) {
            return null;
        }

        return TaskDTO::fromObject($task);
    }

    /**
     * @throws NestedValidationException
     */
    public function createTask(TaskDTO $dto): TaskDTO
    {
        $dto->validate();
        $task = Task::create($dto->toArray());
        return TaskDTO::fromObject($task);
    }

    /**
     * @throws NestedValidationException
     */
    public function updateTask($id, object $data): bool
    {
        $task = Task::find($id);

        if (!$task) {
            return false;
        }

        // Preenche o modelo com os novos dados
        $task->fill((array) $data);

        // Cria um DTO a partir do estado atualizado do modelo para validação
        $taskDto = TaskDTO::fromObject($task);
        $taskDto->validate();

        // Salva o modelo, que já foi atualizado
        return $task->save();
    }

    public function updateTaskStatus(int $id, string $status): bool
    {
        $task = Task::find($id);

        if (!$task) {
            return false;
        }

        // Usa o método fill para consistência
        $task->fill(['status' => $status]);

        // Cria um DTO para garantir que o estado final seja válido
        $taskDto = TaskDTO::fromObject($task);
        $taskDto->validate();

        return $task->save();
    }

    public function deleteTask(int $id): bool
    {
        return Task::destroy($id) > 0;
    }
}