import React, { useState, useEffect, useCallback } from 'react';
import api from './services/api';
import TaskList from './components/TaskList';
import TaskForm from './components/TaskForm';

function App() {
  const [tasks, setTasks] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  const fetchTasks = useCallback(() => {
    setLoading(true);
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

  return (
    <div className="App">
      <header>
        <h1>Appointment Calendar</h1>
      </header>
      <main>
        <TaskForm onTaskCreated={fetchTasks} />
        <TaskList tasks={tasks} loading={loading} error={error} />
      </main>
    </div>
  );
}

export default App;
