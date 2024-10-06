<?php
namespace App\Controllers;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class TaskController {

  // public function list(Request $request, $id): Response
  // public function list($id, Request $request): Response
  public function list($id): Response
  {
      // ...
      dump($request ?? '', $id);
      return new Response('Task List');
  }

  public function index() {
    // Lógica para listar todas as tarefas
      return new Response('Task Index');
  }

  public function store() {
    // Lógica para adicionar uma nova tarefa
  }

  // ... outros métodos
}
