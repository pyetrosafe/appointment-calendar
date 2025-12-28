import React, { useState } from 'react';
import api from '../services/api';

const TaskForm = ({ onTaskCreated }) => {
  const [title, setTitle] = useState('');
  const [description, setDescription] = useState('');
  const [dueDate, setDueDate] = useState('');
  const [error, setError] = useState('');
  const [submitting, setSubmitting] = useState(false);

  const handleSubmit = (e) => {
    e.preventDefault();
    if (!title) {
      setError('O título é obrigatório.');
      return;
    }

    setSubmitting(true);
    setError('');

    const newTask = {
      title,
      description,
      due_date: dueDate || null,
    };

    api.post('/task', newTask)
      .then(() => {
        // Limpa o formulário
        setTitle('');
        setDescription('');
        setDueDate('');
        // Notifica o componente pai para recarregar a lista
        if (onTaskCreated) {
          onTaskCreated();
        }
      })
      .catch(err => {
        console.error('Erro ao criar tarefa:', err);
        setError('Falha ao criar a tarefa. Tente novamente.');
      })
      .finally(() => {
        setSubmitting(false);
      });
  };

  return (
    <form onSubmit={handleSubmit}>
      <h3>Nova Tarefa</h3>
      {error && <p style={{ color: 'red' }}>{error}</p>}
      <div>
        <input type="text" value={title} onChange={e => setTitle(e.target.value)} placeholder="Título da tarefa" />
      </div>
      <div>
        <textarea value={description} onChange={e => setDescription(e.target.value)} placeholder="Descrição"></textarea>
      </div>
      <div>
        <input type="datetime-local" value={dueDate} onChange={e => setDueDate(e.target.value)} />
      </div>
      <button type="submit" disabled={submitting}>
        {submitting ? 'Salvando...' : 'Salvar Tarefa'}
      </button>
    </form>
  );
};

export default TaskForm;