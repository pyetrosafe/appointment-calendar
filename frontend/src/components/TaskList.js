import React from 'react';
// import TaskItem from './TaskItem'; // Podemos reintegrar o TaskItem depois se ele existir

const TaskList = ({ tasks, loading, error }) => {
  if (loading) return <p>Carregando...</p>;
  if (error) return <p>{error}</p>;

  return (
    <div>
      <h2>Minhas Tarefas</h2>
      {tasks && tasks.length > 0 ? (
        <ul>
          {tasks.map(task => (
            <li key={task.id}>
              <strong>{task.title}</strong>
              {task.description && <p>{task.description}</p>}
              {task.due_date && (
                <small>
                  Vence em: {new Date(task.due_date).toLocaleDateString('pt-BR')}
                </small>
              )}
            </li>
          ))}
        </ul>
      ) : (
        <p>Nenhuma tarefa encontrada.</p>
      )}
    </div>
  );
};

export default TaskList;