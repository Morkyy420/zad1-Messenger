<?php

namespace App\Controller;

use App\Entity\Conversation;
use App\Entity\Message;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api')]
class ChatController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserRepository $userRepository,
        private SerializerInterface $serializer
    ) {
    }

    #[Route('/messages', name: 'post_message', methods: ['POST'])]
    public function postMessage(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);

        // For now, let's assume user ID 1 is the sender.
        // In a real app, you would get the user from the security context.
        $author = $this->userRepository->find(1);
        if (!$author) {
            // Create a dummy user if one doesn't exist for testing
            $author = new \App\Entity\User();
            $author->setUsername('TestUser');
            $this->entityManager->persist($author);
            $this->entityManager->flush();
        }

        // For now, let's use a single conversation (ID 1).
        // In a real app, you'd get this from the request or other logic.
        $conversation = $this->entityManager->getRepository(Conversation::class)->find(1);
        if (!$conversation) {
            $conversation = new Conversation();
            $conversation->addParticipant($author);
            $this->entityManager->persist($conversation);
            $this->entityManager->flush();
        }

        $message = new Message();
        $message->setAuthor($author);
        $message->setContent($data['content']);
        $message->setConversation($conversation);

        $this->entityManager->persist($message);
        $this->entityManager->flush();

        // Serialize the message to JSON
        $jsonMessage = $this->serializer->serialize($message, 'json', [
            'groups' => ['message:read'],
        ]);

        return new Response($jsonMessage, Response::HTTP_CREATED, [
            'Content-Type' => 'application/json',
        ]);
    }

    #[Route('/messages', name: 'get_messages', methods: ['GET'])]
    public function getMessages(): Response
    {
        // Get all messages from the first conversation
        $conversation = $this->entityManager->getRepository(Conversation::class)->find(1);

        $messages = [];
        if ($conversation) {
            $messages = $this->entityManager->getRepository(Message::class)
                ->findBy(['conversation' => $conversation], ['createdAt' => 'ASC']);
        }

        $jsonMessages = $this->serializer->serialize($messages, 'json', [
            'groups' => ['message:read'],
        ]);

        return new Response($jsonMessages, Response::HTTP_OK, [
            'Content-Type' => 'application/json',
        ]);
    }

    #[Route('/messages/{id}/reaction', name: 'add_reaction', methods: ['POST'])]
    public function addReaction(int $id, Request $request): Response
    {
        $data = json_decode($request->getContent(), true);
        $reactionType = $data['type'] ?? null;

        if (!in_array($reactionType, ['heart', 'like', 'laugh', 'wow'])) {
            return new Response(json_encode(['error' => 'Invalid reaction type']),
                Response::HTTP_BAD_REQUEST, [
                'Content-Type' => 'application/json',
            ]);
        }

        $message = $this->entityManager->getRepository(Message::class)->find($id);

        if (!$message) {
            return new Response(json_encode(['error' => 'Message not found']),
                Response::HTTP_NOT_FOUND, [
                'Content-Type' => 'application/json',
            ]);
        }

        $message->addReaction($reactionType);
        $this->entityManager->flush();

        $jsonMessage = $this->serializer->serialize($message, 'json', [
            'groups' => ['message:read'],
        ]);

        return new Response($jsonMessage, Response::HTTP_OK, [
            'Content-Type' => 'application/json',
        ]);
    }
}
