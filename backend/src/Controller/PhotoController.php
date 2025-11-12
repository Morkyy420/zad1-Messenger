<?php

namespace App\Controller;

use App\Entity\Photo;
use App\Repository\PhotoRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/photos')]
class PhotoController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private PhotoRepository $photoRepository,
        private UserRepository $userRepository
    ) {
    }

    #[Route('/{userId}', name: 'get_user_photos', methods: ['GET'])]
    public function getUserPhotos(int $userId): JsonResponse
    {
        $user = $this->userRepository->find($userId);
        if (!$user) {
            return new JsonResponse(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        $photos = $this->photoRepository->findByUser($userId);

        $photosData = [];
        foreach ($photos as $photo) {
            $photosData[] = [
                'id' => $photo->getId(),
                'url' => $photo->getUrl(),
                'caption' => $photo->getCaption(),
                'originalName' => $photo->getOriginalName(),
                'uploadedAt' => $photo->getUploadedAt()->format('Y-m-d H:i:s')
            ];
        }

        return new JsonResponse($photosData, Response::HTTP_OK);
    }

    #[Route('', name: 'upload_photo', methods: ['POST'])]
    public function uploadPhoto(Request $request): JsonResponse
    {
        $file = $request->files->get('file');
        $userId = $request->request->get('userId');
        $caption = $request->request->get('caption');

        if (!$file) {
            return new JsonResponse(['error' => 'No file uploaded'], Response::HTTP_BAD_REQUEST);
        }

        if (!$userId) {
            return new JsonResponse(['error' => 'User ID required'], Response::HTTP_BAD_REQUEST);
        }

        $user = $this->userRepository->find($userId);
        if (!$user) {
            return new JsonResponse(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        // Validate file type - images and videos
        $allowedMimeTypes = [
            'image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp', 'image/bmp', 'image/x-ms-bmp',
            'video/mp4', 'video/quicktime', 'video/x-msvideo'
        ];

        $mimeType = $file->getMimeType();
        if (!in_array($mimeType, $allowedMimeTypes)) {
            return new JsonResponse([
                'error' => 'Invalid file type. Only images (JPEG, JPG, PNG, GIF, WebP, BMP) and videos (MP4) allowed',
                'receivedMimeType' => $mimeType,
                'allowedTypes' => $allowedMimeTypes
            ], Response::HTTP_BAD_REQUEST);
        }

        // Validate file size (max 10MB for images, 50MB for videos)
        $maxSize = str_starts_with($mimeType, 'video/') ? 50 * 1024 * 1024 : 10 * 1024 * 1024;
        if ($file->getSize() > $maxSize) {
            $limit = str_starts_with($mimeType, 'video/') ? '50MB' : '10MB';
            return new JsonResponse(['error' => "File too large (max {$limit})"], Response::HTTP_BAD_REQUEST);
        }

        // Use absolute path for uploads directory
        $projectDir = dirname(__DIR__, 2);
        $uploadsDir = $projectDir . '/public/uploads/gallery';

        // Create uploads directory if it doesn't exist
        if (!is_dir($uploadsDir)) {
            if (!mkdir($uploadsDir, 0755, true)) {
                return new JsonResponse([
                    'error' => 'Failed to create uploads directory'
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }

        // Generate unique filename
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = preg_replace('/[^a-zA-Z0-9_-]/', '_', $originalFilename);
        $extension = $file->guessExtension();
        if (!$extension) {
            $extension = 'jpg';
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

        // Get the base URL from request
        $scheme = $request->getScheme();
        $host = $request->getHttpHost();
        $baseUrl = $scheme . '://' . $host;

        // Create Photo entity
        $photo = new Photo();
        $photo->setUser($user);
        $photo->setUrl($baseUrl . '/uploads/gallery/' . $newFilename);
        $photo->setOriginalName($file->getClientOriginalName());
        $photo->setCaption($caption);

        $this->entityManager->persist($photo);
        $this->entityManager->flush();

        return new JsonResponse([
            'id' => $photo->getId(),
            'url' => $photo->getUrl(),
            'caption' => $photo->getCaption(),
            'originalName' => $photo->getOriginalName(),
            'uploadedAt' => $photo->getUploadedAt()->format('Y-m-d H:i:s')
        ], Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'update_photo_caption', methods: ['PATCH'])]
    public function updatePhotoCaption(int $id, Request $request): JsonResponse
    {
        $photo = $this->photoRepository->find($id);

        if (!$photo) {
            return new JsonResponse(['error' => 'Photo not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['caption'])) {
            $photo->setCaption($data['caption']);
        }

        $this->entityManager->flush();

        return new JsonResponse([
            'id' => $photo->getId(),
            'caption' => $photo->getCaption()
        ], Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'delete_photo', methods: ['DELETE'])]
    public function deletePhoto(int $id): JsonResponse
    {
        $photo = $this->photoRepository->find($id);

        if (!$photo) {
            return new JsonResponse(['error' => 'Photo not found'], Response::HTTP_NOT_FOUND);
        }

        // Delete file from filesystem
        $url = $photo->getUrl();
        $path = parse_url($url, PHP_URL_PATH);
        $filePath = dirname(__DIR__, 2) . '/public' . $path;

        if (file_exists($filePath)) {
            unlink($filePath);
        }

        $this->entityManager->remove($photo);
        $this->entityManager->flush();

        return new JsonResponse(['success' => true], Response::HTTP_OK);
    }
}
