<?php

namespace App\Controller;

use App\Entity\Conversation;
use App\Entity\Message;
use App\Repository\ConversationRepository;
use App\Repository\MessageRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/conversations')]
class ConversationController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ConversationRepository $conversationRepository,
        private MessageRepository $messageRepository,
        private UserRepository $userRepository,
        private SerializerInterface $serializer
    ) {
    }

    #[Route('/{userId}', name: 'get_user_conversations', methods: ['GET'])]
    public function getUserConversations(int $userId): JsonResponse
    {
        $user = $this->userRepository->find($userId);
        if (!$user) {
            return new JsonResponse(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        $conversations = $this->conversationRepository->findConversationsForUser($userId);

        $conversationsData = [];
        foreach ($conversations as $conversation) {
            $otherUser = $conversation->getOtherParticipant($user);
            $lastMessage = $conversation->getLastMessage();

            if ($otherUser && $lastMessage) {
                $conversationsData[] = [
                    'id' => $conversation->getId(),
                    'participant' => [
                        'id' => $otherUser->getId(),
                        'username' => $otherUser->getUsername(),
                        'email' => $otherUser->getEmail(),
                        'avatar' => $otherUser->getAvatar()
                    ],
                    'lastMessage' => [
                        'id' => $lastMessage->getId(),
                        'content' => $lastMessage->getContent(),
                        'createdAt' => $lastMessage->getCreatedAt()->format('Y-m-d H:i:s'),
                        'readAt' => $lastMessage->getReadAt()?->format('Y-m-d H:i:s'),
                        'isRead' => $lastMessage->getReadAt() !== null,
                        'authorId' => $lastMessage->getAuthor()->getId()
                    ]
                ];
            }
        }

        return new JsonResponse($conversationsData, Response::HTTP_OK);
    }

    #[Route('/{userId}/with/{friendId}', name: 'get_or_create_conversation', methods: ['GET'])]
    public function getOrCreateConversation(int $userId, int $friendId): JsonResponse
    {
        $user = $this->userRepository->find($userId);
        $friend = $this->userRepository->find($friendId);

        if (!$user || !$friend) {
            return new JsonResponse(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        // Try to find existing conversation
        $conversation = $this->conversationRepository->findConversationBetweenUsers($userId, $friendId);

        // Create new conversation if doesn't exist
        if (!$conversation) {
            $conversation = new Conversation();
            $conversation->addParticipant($user);
            $conversation->addParticipant($friend);
            $this->entityManager->persist($conversation);
            $this->entityManager->flush();
        }

        return new JsonResponse([
            'id' => $conversation->getId(),
            'participant' => [
                'id' => $friend->getId(),
                'username' => $friend->getUsername(),
                'email' => $friend->getEmail(),
                'avatar' => $friend->getAvatar()
            ]
        ], Response::HTTP_OK);
    }

    #[Route('/{conversationId}/messages', name: 'get_conversation_messages', methods: ['GET'])]
    public function getMessages(int $conversationId): JsonResponse
    {
        $conversation = $this->conversationRepository->find($conversationId);

        if (!$conversation) {
            return new JsonResponse(['error' => 'Conversation not found'], Response::HTTP_NOT_FOUND);
        }

        $messages = $this->messageRepository->findBy(
            ['conversation' => $conversation],
            ['createdAt' => 'ASC']
        );

        $jsonMessages = $this->serializer->serialize($messages, 'json', [
            'groups' => ['message:read'],
        ]);

        return new JsonResponse(json_decode($jsonMessages, true), Response::HTTP_OK);
    }

    #[Route('/{conversationId}/messages', name: 'post_message_to_conversation', methods: ['POST'])]
    public function postMessage(int $conversationId, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $conversation = $this->conversationRepository->find($conversationId);
        if (!$conversation) {
            return new JsonResponse(['error' => 'Conversation not found'], Response::HTTP_NOT_FOUND);
        }

        $author = $this->userRepository->find($data['authorId']);
        if (!$author) {
            return new JsonResponse(['error' => 'Author not found'], Response::HTTP_NOT_FOUND);
        }

        $message = new Message();
        $message->setAuthor($author);
        $message->setContent($data['content'] ?? '');
        $message->setConversation($conversation);

        // Handle attachments if present
        if (isset($data['attachmentType']) && isset($data['attachmentUrl'])) {
            $message->setAttachmentType($data['attachmentType']);
            $message->setAttachmentUrl($data['attachmentUrl']);
            $message->setAttachmentName($data['attachmentName'] ?? null);
        }

        $this->entityManager->persist($message);
        $this->entityManager->flush();

        $jsonMessage = $this->serializer->serialize($message, 'json', [
            'groups' => ['message:read'],
        ]);

        return new JsonResponse(json_decode($jsonMessage, true), Response::HTTP_CREATED);
    }

    #[Route('/{conversationId}/messages/upload', name: 'upload_message_attachment', methods: ['POST', 'OPTIONS'])]
    public function uploadAttachment(int $conversationId, Request $request): JsonResponse
    {
        // Handle CORS preflight
        if ($request->getMethod() === 'OPTIONS') {
            return new JsonResponse(null, Response::HTTP_NO_CONTENT);
        }

        $conversation = $this->conversationRepository->find($conversationId);
        if (!$conversation) {
            return new JsonResponse(['error' => 'Conversation not found'], Response::HTTP_NOT_FOUND);
        }

        $file = $request->files->get('file');
        if (!$file) {
            return new JsonResponse(['error' => 'No file uploaded'], Response::HTTP_BAD_REQUEST);
        }

        // Validate file type
        $allowedMimeTypes = [
            'image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp',
            'video/mp4', 'video/webm', 'video/ogg', 'video/quicktime'
        ];

        $mimeType = $file->getMimeType();
        if (!in_array($mimeType, $allowedMimeTypes)) {
            return new JsonResponse([
                'error' => 'Invalid file type. Allowed: images and videos only',
                'mimeType' => $mimeType
            ], Response::HTTP_BAD_REQUEST);
        }

        // Validate file size (max 50MB)
        if ($file->getSize() > 50 * 1024 * 1024) {
            return new JsonResponse(['error' => 'File too large (max 50MB)'], Response::HTTP_BAD_REQUEST);
        }

        // Use absolute path for uploads directory
        $projectDir = dirname(__DIR__, 2); // Go up from src/Controller to project root
        $uploadsDir = $projectDir . '/public/uploads/chat';

        // Create uploads directory if it doesn't exist
        if (!is_dir($uploadsDir)) {
            if (!mkdir($uploadsDir, 0755, true)) {
                return new JsonResponse([
                    'error' => 'Failed to create uploads directory',
                    'path' => $uploadsDir
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }

        // Generate unique filename
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = preg_replace('/[^a-zA-Z0-9_-]/', '_', $originalFilename);
        $extension = $file->guessExtension();
        if (!$extension) {
            $extension = 'bin';
        }
        $newFilename = $safeFilename . '_' . uniqid() . '.' . $extension;

        // Move file to uploads directory
        try {
            $file->move($uploadsDir, $newFilename);
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'Failed to upload file: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        // Determine attachment type
        $attachmentType = str_starts_with($mimeType, 'image/') ? 'image' : 'video';

        // Get the base URL from request
        $scheme = $request->getScheme();
        $host = $request->getHttpHost();
        $baseUrl = $scheme . '://' . $host;

        return new JsonResponse([
            'success' => true,
            'attachmentType' => $attachmentType,
            'attachmentUrl' => $baseUrl . '/uploads/chat/' . $newFilename,
            'attachmentName' => $file->getClientOriginalName()
        ], Response::HTTP_OK);
    }

    #[Route('/messages/{messageId}/read', name: 'mark_message_read', methods: ['POST'])]
    public function markMessageAsRead(int $messageId): JsonResponse
    {
        $message = $this->messageRepository->find($messageId);

        if (!$message) {
            return new JsonResponse(['error' => 'Message not found'], Response::HTTP_NOT_FOUND);
        }

        if (!$message->getReadAt()) {
            $message->setReadAt(new \DateTimeImmutable());
            $this->entityManager->flush();
        }

        return new JsonResponse(['success' => true], Response::HTTP_OK);
    }

    #[Route('/{conversationId}/messages/mark-read', name: 'mark_conversation_read', methods: ['POST'])]
    public function markConversationAsRead(int $conversationId, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $userId = $data['userId'] ?? null;

        if (!$userId) {
            return new JsonResponse(['error' => 'User ID required'], Response::HTTP_BAD_REQUEST);
        }

        $conversation = $this->conversationRepository->find($conversationId);
        if (!$conversation) {
            return new JsonResponse(['error' => 'Conversation not found'], Response::HTTP_NOT_FOUND);
        }

        // Mark all messages from other participant as read
        $messages = $this->messageRepository->findBy(['conversation' => $conversation]);

        foreach ($messages as $message) {
            if ($message->getAuthor()->getId() !== $userId && !$message->getReadAt()) {
                $message->setReadAt(new \DateTimeImmutable());
            }
        }

        $this->entityManager->flush();

        return new JsonResponse(['success' => true], Response::HTTP_OK);
    }

    #[Route('/{conversationId}/messages/{messageId}/reaction', name: 'add_message_reaction', methods: ['POST'])]
    public function addReaction(int $conversationId, int $messageId, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $reactionType = $data['type'] ?? null;

        if (!in_array($reactionType, ['heart', 'like', 'laugh', 'wow'])) {
            return new JsonResponse(['error' => 'Invalid reaction type'], Response::HTTP_BAD_REQUEST);
        }

        $message = $this->messageRepository->find($messageId);

        if (!$message) {
            return new JsonResponse(['error' => 'Message not found'], Response::HTTP_NOT_FOUND);
        }

        $message->addReaction($reactionType);
        $this->entityManager->flush();

        $jsonMessage = $this->serializer->serialize($message, 'json', [
            'groups' => ['message:read'],
        ]);

        return new JsonResponse(json_decode($jsonMessage, true), Response::HTTP_OK);
    }

    #[Route('/messages/{messageId}/edit', name: 'edit_message', methods: ['PUT'])]
    public function editMessage(int $messageId, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['content'])) {
            return new JsonResponse(['error' => 'Content is required'], Response::HTTP_BAD_REQUEST);
        }

        $message = $this->messageRepository->find($messageId);

        if (!$message) {
            return new JsonResponse(['error' => 'Message not found'], Response::HTTP_NOT_FOUND);
        }

        // Optional: Check if user is the author
        if (isset($data['userId'])) {
            $userId = $data['userId'];
            if ($message->getAuthor()->getId() !== $userId) {
                return new JsonResponse(['error' => 'Unauthorized'], Response::HTTP_FORBIDDEN);
            }
        }

        $message->setContent($data['content']);
        $message->setEdited(true);
        $this->entityManager->flush();

        $jsonMessage = $this->serializer->serialize($message, 'json', [
            'groups' => ['message:read'],
        ]);

        return new JsonResponse(json_decode($jsonMessage, true), Response::HTTP_OK);
    }

    #[Route('/messages/{messageId}/delete', name: 'delete_message', methods: ['DELETE'])]
    public function deleteMessage(int $messageId, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $message = $this->messageRepository->find($messageId);

        if (!$message) {
            return new JsonResponse(['error' => 'Message not found'], Response::HTTP_NOT_FOUND);
        }

        // Optional: Check if user is the author
        if (isset($data['userId'])) {
            $userId = $data['userId'];
            if ($message->getAuthor()->getId() !== $userId) {
                return new JsonResponse(['error' => 'Unauthorized'], Response::HTTP_FORBIDDEN);
            }
        }

        $message->setDeleted(true);
        $message->setContent('');
        $this->entityManager->flush();

        $jsonMessage = $this->serializer->serialize($message, 'json', [
            'groups' => ['message:read'],
        ]);

        return new JsonResponse(json_decode($jsonMessage, true), Response::HTTP_OK);
    }

    #[Route('/{conversationId}/typing', name: 'set_typing', methods: ['POST'])]
    public function setTyping(int $conversationId, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $userId = $data['userId'] ?? null;

        if (!$userId) {
            return new JsonResponse(['error' => 'User ID is required'], Response::HTTP_BAD_REQUEST);
        }

        $conversation = $this->conversationRepository->find($conversationId);
        if (!$conversation) {
            return new JsonResponse(['error' => 'Conversation not found'], Response::HTTP_NOT_FOUND);
        }

        // Store typing status in cache or session
        $cacheKey = "typing_{$conversationId}_{$userId}";
        $cacheDir = $this->getParameter('kernel.cache_dir');
        $cacheFile = $cacheDir . '/' . $cacheKey;

        file_put_contents($cacheFile, time());

        return new JsonResponse(['success' => true], Response::HTTP_OK);
    }

    #[Route('/{conversationId}/typing-status', name: 'get_typing_status', methods: ['GET'])]
    public function getTypingStatus(int $conversationId, Request $request): JsonResponse
    {
        $userId = $request->query->get('userId');

        if (!$userId) {
            return new JsonResponse(['error' => 'User ID is required'], Response::HTTP_BAD_REQUEST);
        }

        $conversation = $this->conversationRepository->find($conversationId);
        if (!$conversation) {
            return new JsonResponse(['error' => 'Conversation not found'], Response::HTTP_NOT_FOUND);
        }

        $user = $this->userRepository->find($userId);
        if (!$user) {
            return new JsonResponse(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        // Get the other participant
        $otherUser = $conversation->getOtherParticipant($user);
        if (!$otherUser) {
            return new JsonResponse(['isTyping' => false], Response::HTTP_OK);
        }

        // Check if other user is typing
        $cacheKey = "typing_{$conversationId}_{$otherUser->getId()}";
        $cacheDir = $this->getParameter('kernel.cache_dir');
        $cacheFile = $cacheDir . '/' . $cacheKey;

        if (file_exists($cacheFile)) {
            $lastTyping = (int) file_get_contents($cacheFile);
            $isTyping = (time() - $lastTyping) < 2; // Consider typing if last update was less than 2 seconds ago

            if ($isTyping) {
                return new JsonResponse([
                    'isTyping' => true,
                    'username' => $otherUser->getUsername()
                ], Response::HTTP_OK);
            }
        }

        return new JsonResponse(['isTyping' => false], Response::HTTP_OK);
    }

    #[Route('/{conversationId}/typing/clear', name: 'clear_typing', methods: ['POST'])]
    public function clearTyping(int $conversationId, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $userId = $data['userId'] ?? null;

        if (!$userId) {
            return new JsonResponse(['error' => 'User ID is required'], Response::HTTP_BAD_REQUEST);
        }

        // Remove typing status from cache
        $cacheKey = "typing_{$conversationId}_{$userId}";
        $cacheDir = $this->getParameter('kernel.cache_dir');
        $cacheFile = $cacheDir . '/' . $cacheKey;

        if (file_exists($cacheFile)) {
            unlink($cacheFile);
        }

        return new JsonResponse(['success' => true], Response::HTTP_OK);
    }
}
