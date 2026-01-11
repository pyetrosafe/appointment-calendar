<?php

namespace Services;

use DTOs\TaskDTO;
use Models\Task;
use Respect\Validation\Validator as v;
use Respect\Validation\Exceptions\NestedValidationException;

class TaskService
{
    private Task $taskModel;

    public function __construct()
    {
        $this->taskModel = new Task();
    }

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
        $this->taskModel->create($dto);
        return TaskDTO::fromObject($this->taskModel);
    }

    /**
     * @throws NestedValidationException
     */
    public function updateTask($id, object $data): bool
    {
        // Se nada foi fornecido para atualizar, consideramos a operação bem-sucedida.
        if (empty($data)) {
            return false;
        }

        $taskDto = TaskDTO::fromObject((object) array_merge($this->taskModel->find($id), (array) $data));
        $taskDto->validate();

        return $this->taskModel->update($taskDto);
    }

    public function updateTaskStatus(int $id, string $status): bool
    {
        // if (!in_array($status, ['pending', 'in_progress', 'completed'])) {
        //     return false;
        // }

        $taskDto = TaskDTO::fromObject((object) array_merge($this->taskModel->find($id), ['status' => $status]));
        $taskDto->validate();

        return $this->taskModel->update($taskDto);
    }

    public function deleteTask(int $id): bool
    {
        return $this->taskModel->delete($id);
    }
}