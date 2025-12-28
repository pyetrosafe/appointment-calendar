import React, { useState, useEffect, useCallback } from 'react';
import api from './services/api';
import TaskList from './components/TaskList';
import TaskForm from './components/TaskForm';

function App() {
  const [tasks, setTasks] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [editingTask, setEditingTask] = useState(null);

  const fetchTasks = useCallback(() => {
    setLoading(true);
    setEditingTask(null); // Reseta o formulário de edição
    api.get('/task')
      .then(response => {
        setTasks(response.data);
        setError(null);
      })
      .catch(err => {
        console.error('Erro ao buscar tarefas:', err);
        setError('Falha ao carregar tarefas.');
      })
      .finally(() => {
        setLoading(false);
      });
  }, []);

  useEffect(() => {
    fetchTasks();
  }, [fetchTasks]);

  const handleEdit = (task) => {
    setEditingTask(task);
  };

  const handleDelete = (id) => {
    if (window.confirm('Tem certeza que deseja excluir esta tarefa?')) {
      api.delete(`/task/${id}`)
        .then(() => {
          // Remove a tarefa da lista localmente para uma UI mais rápida
          setTasks(tasks.filter(task => task.id !== id));
        })
        .catch(err => {
          console.error('Erro ao excluir tarefa:', err);
          setError('Falha ao excluir a tarefa.');
        });
    }
  };

  const handleToggleStatus = (id, newStatus) => {
    api.patch(`/task/${id}/status`, { status: newStatus })
      .then(() => {
        setTasks(tasks.map(task =>
          task.id === id ? { ...task, status: newStatus } : task
        ));
      })
      .catch(err => {
        console.error('Erro ao atualizar status da tarefa:', err);
        setError('Falha ao atualizar o status da tarefa.');
      });
  };

  return (
    <div className="App">
      <header>
        <h1>Appointment Calendar</h1>
      </header>
      <main>
        <TaskForm
          onTaskCreated={fetchTasks}
          onTaskUpdated={fetchTasks} // A mesma função de recarregar serve para o update
          editingTask={editingTask}
          setEditingTask={setEditingTask} // Para o form poder se fechar
        />
        <TaskList
          tasks={tasks}
          loading={loading}
          error={error}
          onEdit={handleEdit}
          onDelete={handleDelete}
          onToggleStatus={handleToggleStatus}
        />
      </main>
    </div>
  );
}

export default App;
