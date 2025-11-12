<?php

namespace App\Controller;

use App\Entity\Todo;
use App\Repository\TodoRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/todos')]
class TodoController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private TodoRepository $todoRepository,
        private UserRepository $userRepository
    ) {
    }

    #[Route('/{userId}', name: 'get_user_todos', methods: ['GET'])]
    public function getUserTodos(int $userId): JsonResponse
    {
        $user = $this->userRepository->find($userId);
        if (!$user) {
            return new JsonResponse(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        $todos = $this->todoRepository->findByUser($userId);

        $todosData = [];
        foreach ($todos as $todo) {
            $todosData[] = [
                'id' => $todo->getId(),
                'text' => $todo->getText(),
                'completed' => $todo->isCompleted(),
                'createdAt' => $todo->getCreatedAt()->format('Y-m-d H:i:s')
            ];
        }

        return new JsonResponse($todosData, Response::HTTP_OK);
    }

    #[Route('', name: 'create_todo', methods: ['POST'])]
    public function createTodo(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['text']) || !isset($data['userId'])) {
            return new JsonResponse(['error' => 'Missing required fields'], Response::HTTP_BAD_REQUEST);
        }

        $user = $this->userRepository->find($data['userId']);
        if (!$user) {
            return new JsonResponse(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        $todo = new Todo();
        $todo->setText($data['text']);
        $todo->setUser($user);
        $todo->setCompleted(false);

        $this->entityManager->persist($todo);
        $this->entityManager->flush();

        return new JsonResponse([
            'id' => $todo->getId(),
            'text' => $todo->getText(),
            'completed' => $todo->isCompleted(),
            'createdAt' => $todo->getCreatedAt()->format('Y-m-d H:i:s')
        ], Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'update_todo', methods: ['PATCH'])]
    public function updateTodo(int $id, Request $request): JsonResponse
    {
        $todo = $this->todoRepository->find($id);

        if (!$todo) {
            return new JsonResponse(['error' => 'Todo not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['completed'])) {
            $todo->setCompleted($data['completed']);
        }

        if (isset($data['text'])) {
            $todo->setText($data['text']);
        }

        $this->entityManager->flush();

        return new JsonResponse([
            'id' => $todo->getId(),
            'text' => $todo->getText(),
            'completed' => $todo->isCompleted()
        ], Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'delete_todo', methods: ['DELETE'])]
    public function deleteTodo(int $id): JsonResponse
    {
        $todo = $this->todoRepository->find($id);

        if (!$todo) {
            return new JsonResponse(['error' => 'Todo not found'], Response::HTTP_NOT_FOUND);
        }

        $this->entityManager->remove($todo);
        $this->entityManager->flush();

        return new JsonResponse(['success' => true], Response::HTTP_OK);
    }
}
