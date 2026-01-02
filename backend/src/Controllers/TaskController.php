<?php

namespace Controllers;

use Models\Task;
use Respect\Validation\Validator as v;
use Respect\Validation\Exceptions\NestedValidationException;
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
        $data = json_decode($request->getContent());

        if (!$data) {
             return new JsonResponse(['error' => 'Invalid JSON'], 400);
        }

        try {
            $taskValidator = v::attribute('title', v::stringType()->notEmpty())
                ->attribute('description', v::stringType(), false)
                ->attribute('due_date', v::dateTime('Y-m-d H:i:s'), false);

            $taskValidator->assert($data);
        } catch (NestedValidationException $exception) {
            return new JsonResponse(['errors' => $exception->getMessages()], 422);
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

        $data = json_decode($request->getContent());

        if (!$data) {
             return new JsonResponse(['error' => 'Invalid JSON'], 400);
        }

        try {
            $taskValidator = v::attribute('title', v::stringType()->notEmpty(), false)
                ->attribute('description', v::stringType(), false)
                ->attribute('due_date', v::dateTime('Y-m-d H:i:s'), false)
                ->attribute('status', v::in(['pending', 'completed']), false);

            $taskValidator->assert($data);
        } catch (NestedValidationException $exception) {
            return new JsonResponse(['errors' => $exception->getMessages()], 422);
        }

        $success = $this->taskModel->update($id, $data);

        if ($success) {
            return new JsonResponse(['message' => 'Task updated']);
        }

        return new JsonResponse(['error' => 'Failed to update task'], 500);
    }

    public function updateStatus(int $id): Response
    {
        $request = Request::createFromGlobals();
        $data = json_decode($request->getContent());

        if (!isset($data->status) || !in_array($data->status, ['pending', 'completed'])) {
            return new JsonResponse(['error' => 'Invalid status provided'], 400);
        }

        $success = $this->taskModel->update($id, $data);

        if ($success) {
            return new JsonResponse(['message' => 'Task status updated']);
        }

        return new JsonResponse(['error' => 'Failed to update task status'], 500);
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