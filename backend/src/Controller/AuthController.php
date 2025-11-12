<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/auth')]
class AuthController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher,
        private ValidatorInterface $validator,
        private JWTTokenManagerInterface $jwtManager
    ) {
    }

    #[Route('/register', name: 'register', methods: ['POST'])]
    public function register(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Validate input
        if (!isset($data['email']) || !isset($data['username']) || !isset($data['password'])) {
            return new JsonResponse(['error' => 'Missing required fields'], Response::HTTP_BAD_REQUEST);
        }

        // Check if user already exists
        $existingUser = $this->entityManager->getRepository(User::class)
            ->findOneBy(['email' => $data['email']]);

        if ($existingUser) {
            return new JsonResponse(['error' => 'User with this email already exists'], Response::HTTP_CONFLICT);
        }

        // Create new user
        $user = new User();
        $user->setEmail($data['email']);
        $user->setUsername($data['username']);

        // Hash password
        $hashedPassword = $this->passwordHasher->hashPassword($user, $data['password']);
        $user->setPassword($hashedPassword);

        // Validate user
        $errors = $this->validator->validate($user);
        if (count($errors) > 0) {
            return new JsonResponse(['error' => (string) $errors], Response::HTTP_BAD_REQUEST);
        }

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return new JsonResponse([
            'message' => 'User registered successfully',
            'user' => [
                'id' => $user->getId(),
                'username' => $user->getUsername(),
                'email' => $user->getEmail()
            ]
        ], Response::HTTP_CREATED);
    }

    #[Route('/login', name: 'login', methods: ['POST'])]
    public function login(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Support login by email or username
        if ((!isset($data['email']) && !isset($data['username'])) || !isset($data['password'])) {
            return new JsonResponse(['error' => 'Missing login credentials'], Response::HTTP_BAD_REQUEST);
        }

        // Find user by email or username
        if (isset($data['email'])) {
            $user = $this->entityManager->getRepository(User::class)
                ->findOneBy(['email' => $data['email']]);
        } else {
            $user = $this->entityManager->getRepository(User::class)
                ->findOneBy(['username' => $data['username']]);
        }

        if (!$user) {
            return new JsonResponse(['error' => 'Invalid credentials'], Response::HTTP_UNAUTHORIZED);
        }

        // Verify password
        if (!$this->passwordHasher->isPasswordValid($user, $data['password'])) {
            return new JsonResponse(['error' => 'Invalid credentials'], Response::HTTP_UNAUTHORIZED);
        }

        // Get user's IP address
        $clientIp = $request->getClientIp();

        // Generate JWT token
        $token = $this->jwtManager->create($user);

        return new JsonResponse([
            'message' => 'Login successful',
            'token' => $token,
            'user' => [
                'id' => $user->getId(),
                'username' => $user->getUsername(),
                'email' => $user->getEmail(),
                'avatar' => $user->getAvatar()
            ],
            'ip' => $clientIp
        ], Response::HTTP_OK);
    }

    #[Route('/ip', name: 'get_ip', methods: ['GET'])]
    public function getClientIp(Request $request): JsonResponse
    {
        $clientIp = $request->getClientIp();
        return new JsonResponse(['ip' => $clientIp], Response::HTTP_OK);
    }

    #[Route('/users', name: 'get_users', methods: ['GET'])]
    public function getUsers(): JsonResponse
    {
        $users = $this->entityManager->getRepository(User::class)->findAll();

        $userData = array_map(function($user) {
            return [
                'id' => $user->getId(),
                'username' => $user->getUsername(),
                'email' => $user->getEmail(),
                'avatar' => $user->getAvatar()
            ];
        }, $users);

        return new JsonResponse($userData, Response::HTTP_OK);
    }

    #[Route('/users/{id}/friends', name: 'add_friend', methods: ['POST'])]
    public function addFriend(int $id, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['currentUserId'])) {
            return new JsonResponse(['error' => 'Missing current user ID'], Response::HTTP_BAD_REQUEST);
        }

        $currentUser = $this->entityManager->getRepository(User::class)->find($data['currentUserId']);
        $friendUser = $this->entityManager->getRepository(User::class)->find($id);

        if (!$currentUser || !$friendUser) {
            return new JsonResponse(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        $currentUser->addFriend($friendUser);
        $friendUser->addFriend($currentUser);

        $this->entityManager->flush();

        return new JsonResponse(['message' => 'Friend added successfully'], Response::HTTP_OK);
    }

    #[Route('/users/{id}/friends', name: 'get_friends', methods: ['GET'])]
    public function getFriends(int $id): JsonResponse
    {
        $user = $this->entityManager->getRepository(User::class)->find($id);

        if (!$user) {
            return new JsonResponse(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        $friends = $user->getFriends();
        $friendsData = [];

        foreach ($friends as $friend) {
            $friendsData[] = [
                'id' => $friend->getId(),
                'username' => $friend->getUsername(),
                'email' => $friend->getEmail(),
                'avatar' => $friend->getAvatar()
            ];
        }

        return new JsonResponse($friendsData, Response::HTTP_OK);
    }

    #[Route('/users/{id}/upload-photo', name: 'upload_photo', methods: ['POST'])]
    public function uploadProfilePhoto(int $id, Request $request): JsonResponse
    {
        $user = $this->entityManager->getRepository(User::class)->find($id);

        if (!$user) {
            return new JsonResponse(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        $file = $request->files->get('file');

        if (!$file) {
            return new JsonResponse(['error' => 'No file uploaded'], Response::HTTP_BAD_REQUEST);
        }

        // Validate file type
        $mimeType = $file->getMimeType();
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

        if (!in_array($mimeType, $allowedTypes)) {
            return new JsonResponse(['error' => 'Invalid file type. Only images are allowed'], Response::HTTP_BAD_REQUEST);
        }

        // Validate file size (max 5MB)
        if ($file->getSize() > 5 * 1024 * 1024) {
            return new JsonResponse(['error' => 'File too large (max 5MB)'], Response::HTTP_BAD_REQUEST);
        }

        // Create uploads directory if it doesn't exist
        $projectDir = dirname(__DIR__, 2);
        $uploadsDir = $projectDir . '/public/uploads/avatars';

        if (!is_dir($uploadsDir)) {
            mkdir($uploadsDir, 0777, true);
        }

        // Generate unique filename
        $extension = $file->guessExtension();
        $filename = 'avatar_' . $user->getId() . '_' . uniqid() . '.' . $extension;

        try {
            // Move file to uploads directory
            $file->move($uploadsDir, $filename);

            // Delete old avatar if exists
            $oldAvatar = $user->getAvatar();
            if ($oldAvatar && file_exists($projectDir . $oldAvatar)) {
                unlink($projectDir . $oldAvatar);
            }

            // Update user avatar
            $avatarPath = '/uploads/avatars/' . $filename;
            $user->setAvatar($avatarPath);
            $this->entityManager->flush();

            return new JsonResponse([
                'message' => 'Profile photo uploaded successfully',
                'avatar' => $avatarPath
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Failed to upload file: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
