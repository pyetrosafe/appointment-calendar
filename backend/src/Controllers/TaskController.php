<?php

namespace App\Controllers;

use Models\Task;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TaskController
{
    private Task $taskModel;

    public function __construct()
    {
        $this->taskModel = new Task();
    }

    public function index(): Response
    {
        $tasks = $this->taskModel->get();
        return new JsonResponse($tasks);
    }

    public function store(): Response
    {
        $request = Request::createFromGlobals();
        $data = json_decode($request->getContent(), true);

        if (!$data) {
             return new JsonResponse(['error' => 'Invalid JSON'], 400);
        }

        $id = $this->taskModel->create($data);

        if ($id) {
            return new JsonResponse(['id' => $id, 'message' => 'Task created'], 201);
        }

        return new JsonResponse(['error' => 'Failed to create task'], 500);
    }

    public function show(int $id): Response
    {

        $task = $this->taskModel->find($id);

        if (!$task) {
            return new JsonResponse(['error' => 'Task not found'], 404);
        }

        return new JsonResponse($task);
    }

    public function update(int $id): Response
    {
        $request = Request::createFromGlobals();

        $data = json_decode($request->getContent(), true);

        if (!$data) {
             return new JsonResponse(['error' => 'Invalid JSON'], 400);
        }

        $success = $this->taskModel->update($id, $data);

        if ($success) {
            return new JsonResponse(['message' => 'Task updated']);
        }

        return new JsonResponse(['error' => 'Failed to update task'], 500);
    }

    public function delete(int $id): Response
    {

        $success = $this->taskModel->delete($id);

        if ($success) {
            return new JsonResponse(['message' => 'Task deleted']);
        }

        return new JsonResponse(['error' => 'Failed to delete task'], 500);
    }
}