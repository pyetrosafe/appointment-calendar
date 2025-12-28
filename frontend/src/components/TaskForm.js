import React, { useState, useEffect } from 'react';
import api from '../services/api';

const TaskForm = ({ onTaskCreated, onTaskUpdated, editingTask, setEditingTask }) => {
  const [title, setTitle] = useState('');
  const [description, setDescription] = useState('');
  const [dueDate, setDueDate] = useState('');
  const [error, setError] = useState('');
  const [submitting, setSubmitting] = useState(false);

  const isEditing = !!editingTask;

  useEffect(() => {
    if (editingTask) {
      setTitle(editingTask.title);
      setDescription(editingTask.description || '');
      // Formata a data para o input datetime-local (YYYY-MM-DDTHH:mm)
      const formattedDate = editingTask.due_date 
        ? new Date(editingTask.due_date).toISOString().slice(0, 16) 
        : '';
      setDueDate(formattedDate);
    } else {
      // Limpa o formulário quando não está em modo de edição
      setTitle('');
      setDescription('');
      setDueDate('');
    }
  }, [editingTask]);

  const handleSubmit = (e) => {
    e.preventDefault();
    if (!title) {
      setError('O título é obrigatório.');
      return;
    }

    setSubmitting(true);
    setError('');

    const taskData = {
      title,
      description,
      due_date: dueDate || null,
    };

    const request = isEditing
      ? api.put(`/task/${editingTask.id}`, taskData)
      : api.post('/task', taskData);

    request
      .then(() => {
        if (isEditing) {
          onTaskUpdated(); // Callback para atualizar a lista
        } else {
          onTaskCreated(); // Callback para criar
        }
        // Limpa e reseta o formulário
        setTitle('');
        setDescription('');
        setDueDate('');
        setEditingTask(null); // Sai do modo de edição
      })
      .catch(err => {
        console.error(`Erro ao ${isEditing ? 'atualizar' : 'criar'} tarefa:`, err);
        setError(`Falha ao ${isEditing ? 'atualizar' : 'criar'} a tarefa. Tente novamente.`);
      })
      .finally(() => {
        setSubmitting(false);
      });
  };

  return (
    <form onSubmit={handleSubmit}>
      <h3>{isEditing ? 'Editar Tarefa' : 'Nova Tarefa'}</h3>
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
      <div className="form-actions">
        <button type="submit" disabled={submitting}>
          {submitting ? 'Salvando...' : (isEditing ? 'Atualizar Tarefa' : 'Salvar Tarefa')}
        </button>
        {isEditing && (
          <button type="button" onClick={() => setEditingTask(null)} disabled={submitting}>
            Cancelar
          </button>
        )}
      </div>
    </form>
  );
};

export default TaskForm;