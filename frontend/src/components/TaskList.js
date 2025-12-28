import React from 'react';
// import TaskItem from './TaskItem'; // Podemos reintegrar o TaskItem depois se ele existir

const TaskList = ({ tasks, loading, error, onEdit, onDelete, onToggleStatus }) => {
  if (loading) return <p className="loading">Carregando...</p>;
  if (error) return <p className="error">{error}</p>;

  return (
    <div className="task-list">
      <h2>Minhas Tarefas</h2>
      {tasks && tasks.length > 0 ? (
        <ul>
          {tasks.map(task => (
            <li key={task.id} className={task.status === 'completed' ? 'completed' : ''}>
              <div className="task-info">
                <input
                  type="checkbox"
                  checked={task.status === 'completed'}
                  onChange={() => onToggleStatus(task.id, task.status === 'completed' ? 'pending' : 'completed')}
                />
                <strong>{task.title}</strong>
              </div>
              {task.description && <p>{task.description}</p>}
              {task.due_date && (
                <small>
                  Vence em: {new Date(task.due_date).toLocaleDateString('pt-BR')}
                </small>
              )}
              <div className="task-actions">
                <button className="edit-btn" onClick={() => onEdit(task)}>Editar</button>
                <button className="delete-btn" onClick={() => onDelete(task.id)}>Excluir</button>
              </div>
            </li>
          ))}
        </ul>
      ) : (
        <p className="no-tasks">Nenhuma tarefa encontrada.</p>
      )}
    </div>
  );
};

export default TaskList;