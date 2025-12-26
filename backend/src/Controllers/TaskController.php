<?php
namespace App\Controllers;

use Models\Task;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class TaskController {

  /**
   * Retorna uma única tarefa por ID.
   */
  public function show(int $id): Response
  {
      $taskModel = new Task();
      $task = $taskModel->find($id);

      if (!$task) {
          return new JsonResponse(['error' => 'Task not found'], 404);
      }

      return new JsonResponse($task);
  }

  /**
   * Retorna uma lista todas as tarefas.
   */
  public function index(): Response
  {
    $taskModel = new Task();
    $tasks = $taskModel->get();

    return new JsonResponse($tasks);
  }

  public function store() {
    // Lógica para adicionar uma nova tarefa
  }

  // ... outros métodos
}
