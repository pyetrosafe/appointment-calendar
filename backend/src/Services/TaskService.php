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
        $task = Task::find($id);

        if (!$task) {
            return false;
        }

        $taskDto = TaskDTO::fromObject((object) array_merge((array) $task, (array) $data));
        $taskDto->validate();

        $task->fill((array) $taskDto);
        return $task->save();
    }

    public function updateTaskStatus(int $id, string $status): bool
    {
        $task = Task::find($id);

        if (!$task) {
            return false;
        }

        $task->status = $status;

        $taskDto = TaskDTO::fromObject($task);
        $taskDto->validate();

        return $task->save();
    }

    public function deleteTask(int $id): bool
    {
        return $this->taskModel->delete($id);
    }
}