<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Task;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class TaskController extends AbstractController
{
    private EntityManagerInterface $em;
    private AuthorizationCheckerInterface $authChecker;

    public function __construct(EntityManagerInterface $em, AuthorizationCheckerInterface $authChecker)
    {
        $this->em = $em;
        $this->authChecker = $authChecker;
    }

    // Liste des tâches
    #[Route('/api/tasks', methods: ['GET'])]
    public function listTasks(): JsonResponse
    {
        $tasks = $this->em->getRepository(Task::class)->findAll();
        $taskArray = [];

        foreach ($tasks as $task) {
            $taskArray[] = [
                'id' => $task->getId(),
                'title' => $task->getTitle(),
                'description' => $task->getDescription(),
                'status' => $task->getStatus(),
            ];
        }

        return new JsonResponse($taskArray);
    }

    // Ajouter une tâche
    #[Route('/api/tasks', methods: ['POST'])]
    public function addTask(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $task = new Task();
        $task->setTitle($data['title']);
        $task->setDescription($data['description'] ?? null);
        $task->setStatus('pending');

        $this->em->persist($task);
        $this->em->flush();

        return new JsonResponse(['status' => 'Task created'], 201);
    }

    // Supprimer une tâche uniquement si l'utilisateur est admin
    #[Route('/api/tasks/{id}', methods: ['DELETE'])]
    public function deleteTask(int $id): JsonResponse
    {
        // Vérifier si l'utilisateur a le rôle admin
        //if (!$this->getUser() || $this->getUser()->getRoles() !== ['ROLE_ADMIN']) {
        //    return new JsonResponse(['status' => 'Access denied', "user" => $this->getUser()], 403);
        //}

        $task = $this->em->getRepository(Task::class)->find($id);
        if (!$task) {
            return new JsonResponse(['status' => 'Task not found'], 404);
        }

        $this->em->remove($task);
        $this->em->flush();

        return new JsonResponse(['status' => 'Task deleted'], 204);
    }

    // Mettre à jour une tâche
    #[Route('/api/tasks/{id}', methods: ['PUT'])]
    public function updateTask(int $id, Request $request): JsonResponse
    {
        $task = $this->em->getRepository(Task::class)->find($id);
        if (!$task) {
            return new JsonResponse(['status' => 'Task not found'], 404);
        }

        $data = json_decode($request->getContent(), true);
        $task->setTitle($data['title']);
        $task->setDescription($data['description'] ?? $task->getDescription());
        $task->setStatus($data['status'] ?? $task->getStatus());

        $this->em->flush();

        return new JsonResponse(['status' => 'Task updated'], 200);
    }
}
