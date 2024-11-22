import React, { useState, useEffect } from 'react';
import api from './api';
import { useNavigate } from 'react-router-dom';

const Dashboard = () => {  // Le nom du composant commence par une majuscule
    const [tasks, setTasks] = useState([]);
    const [newTask, setNewTask] = useState('');
    const navigate = useNavigate();

    useEffect(() => {
        // Vérifiez si le token est présent dans le localStorage
        const token = localStorage.getItem('token');
        if (!token) {
            navigate('/login'); // Rediriger vers la page login si non authentifié
        }

        // Récupérer les tâches depuis l'API
        api.get('/')
            .then((response) => setTasks(response.data))
            .catch((error) => console.error('Error fetching tasks:', error));
    }, [navigate]);

    const addTask = () => {
        if (!newTask) return; // Ne rien faire si le champ est vide
        api.post('', { title: newTask })
            .then(() => {
                setNewTask('');
                // Mettre à jour la liste des tâches après l'ajout
                api.get('')
                    .then((response) => setTasks(response.data))
                    .catch((error) => console.error('Error fetching tasks:', error));
            })
            .catch((error) => console.error('Error adding task:', error));
    };

    const deleteTask = (id) => {
        api.delete(`/${id}`)
            .then(() => {
                // Mettre à jour la liste des tâches après la suppression
                setTasks(tasks.filter((task) => task.id !== id));
            })
            .catch((error) => console.error('Error deleting task:', error));
    };

    const handleLogout = () => {
        // Supprimer le token de localStorage lors de la déconnexion
        localStorage.removeItem('token');
        navigate('/'); // Rediriger vers la page de login
    };

    return (
        <div>
            <h1>Task Manager</h1>
            <button onClick={handleLogout}>Logout</button>
            <input
                value={newTask}
                onChange={(e) => setNewTask(e.target.value)}
                placeholder="New Task"
            />
            <button onClick={addTask}>Add Task</button>
            <ul>
                {tasks.map((task) => (
                    <li key={task.id}>
                        {task.title}
                        <button onClick={() => deleteTask(task.id)}>Delete</button>
                    </li>
                ))}
            </ul>
        </div>
    );
};

export default Dashboard;  // Le nom du composant doit aussi être en majuscule ici
