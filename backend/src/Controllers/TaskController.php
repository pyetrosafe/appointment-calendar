<?php

namespace Controllers;

use DTOs\TaskDTO;
use DTOs\UpdateTaskDTO;
use Services\TaskService;
use Respect\Validation\Exceptions\NestedValidationException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TaskController
{
    private TaskService $taskService;

    public function __construct()
    {
        $this->taskService = new TaskService();
    }

    public function index(): Response
    {
        $tasks = $this->taskService->getAllTasks();
        return new JsonResponse($tasks);
    }

    public function store(): Response
    {
        $request = Request::createFromGlobals();
        $data = json_decode($request->getContent());

        if (!$data) {
             return new JsonResponse(['error' => 'Invalid JSON'], 400);
        }

        try {
            $taskDTO = TaskDTO::fromObject($data);
            $task = $this->taskService->createTask($taskDTO);

            return new JsonResponse($task, 201);
        }
        catch (NestedValidationException $exception) {
            return new JsonResponse(['errors' => $exception->getMessages()], 422);
        }
    }

    public function show(int $id): Response
    {
        $task = $this->taskService->getTask($id);

        if (!$task) {
            return new JsonResponse(['error' => 'Task not found'], 404);
        }

        return new JsonResponse($task);
    }

    public function update(int $id): Response
    {
        $request = Request::createFromGlobals();
        $data = json_decode($request->getContent());

        if (!$data) {
             return new JsonResponse(['error' => 'Invalid JSON'], 400);
        }

        if (!$this->taskService->getTask($id)) {
            return new JsonResponse(['error' => 'Task not found'], 404);
        }

        try {
            $success = $this->taskService->updateTask($id, $data);

            if ($success) {
                $task = $this->taskService->getTask($id);
                return new JsonResponse($task);
            }

            return new JsonResponse(['error' => 'Failed to update task'], 500);

        } catch (NestedValidationException $exception) {
            return new JsonResponse(['errors' => $exception->getMessages()], 422);
        }
    }

    public function updateStatus(int $id): Response
    {
        $request = Request::createFromGlobals();
        $data = json_decode($request->getContent());

        if (!isset($data->status)) {
            return new JsonResponse(['error' => 'Status property is required'], 400);
        }

        if (!$this->taskService->getTask($id)) {
            return new JsonResponse(['error' => 'Task not found'], 404);
        }

        $success = $this->taskService->updateTaskStatus($id, $data->status);

        if ($success) {
            $task = $this->taskService->getTask($id);
            return new JsonResponse($task);
        }

        return new JsonResponse(['error' => 'Failed to update task status'], 500);
    }

    public function delete(int $id): Response
    {
        if (!$this->taskService->getTask($id)) {
            return new JsonResponse(['error' => 'Task not found'], 404);
        }

        $success = $this->taskService->deleteTask($id);

        if ($success) {
            return new Response(null, 204);
        }

        return new JsonResponse(['error' => 'Failed to delete task'], 500);
    }
}