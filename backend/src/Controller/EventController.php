<?php

namespace App\Controller;

use App\Entity\Event;
use App\Repository\EventRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/events')]
class EventController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private EventRepository $eventRepository,
        private UserRepository $userRepository,
        private SerializerInterface $serializer
    ) {
    }

    #[Route('', name: 'get_all_events', methods: ['GET'])]
    public function getAllEvents(): JsonResponse
    {
        $events = $this->eventRepository->findAllOrdered();

        $eventsData = [];
        foreach ($events as $event) {
            $eventsData[] = [
                'id' => $event->getId(),
                'title' => $event->getTitle(),
                'description' => $event->getDescription(),
                'date' => $event->getDate()->format('Y-m-d'),
                'time' => $event->getTime(),
                'author' => [
                    'id' => $event->getAuthor()->getId(),
                    'username' => $event->getAuthor()->getUsername(),
                ],
                'createdAt' => $event->getCreatedAt()->format('Y-m-d H:i:s')
            ];
        }

        return new JsonResponse($eventsData, Response::HTTP_OK);
    }

    #[Route('', name: 'create_event', methods: ['POST'])]
    public function createEvent(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['title']) || !isset($data['date']) || !isset($data['userId'])) {
            return new JsonResponse(['error' => 'Missing required fields'], Response::HTTP_BAD_REQUEST);
        }

        $user = $this->userRepository->find($data['userId']);
        if (!$user) {
            return new JsonResponse(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        $event = new Event();
        $event->setTitle($data['title']);
        $event->setDescription($data['description'] ?? null);
        $event->setDate(new \DateTime($data['date']));
        $event->setTime($data['time'] ?? '12:00');
        $event->setAuthor($user);

        $this->entityManager->persist($event);
        $this->entityManager->flush();

        return new JsonResponse([
            'id' => $event->getId(),
            'title' => $event->getTitle(),
            'description' => $event->getDescription(),
            'date' => $event->getDate()->format('Y-m-d'),
            'time' => $event->getTime(),
            'author' => [
                'id' => $event->getAuthor()->getId(),
                'username' => $event->getAuthor()->getUsername(),
            ]
        ], Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'delete_event', methods: ['DELETE'])]
    public function deleteEvent(int $id): JsonResponse
    {
        $event = $this->eventRepository->find($id);

        if (!$event) {
            return new JsonResponse(['error' => 'Event not found'], Response::HTTP_NOT_FOUND);
        }

        $this->entityManager->remove($event);
        $this->entityManager->flush();

        return new JsonResponse(['success' => true], Response::HTTP_OK);
    }
}
