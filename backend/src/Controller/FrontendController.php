<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FrontendController extends AbstractController
{
    #[Route('/', name: 'app_home', priority: -1)]
    #[Route('/{route}', name: 'app_frontend', requirements: ['route' => '^(?!api|uploads).*'], priority: -1)]
    public function index(): Response
    {
        $indexPath = $this->getParameter('kernel.project_dir') . '/public/dist/index.html';

        if (!file_exists($indexPath)) {
            throw new \Exception('Frontend build not found. Please run: cd frontend && npm run build');
        }

        $content = file_get_contents($indexPath);

        // Fix asset paths to include /dist/ prefix
        $content = str_replace('="/js/', '="/dist/js/', $content);
        $content = str_replace('="/css/', '="/dist/css/', $content);

        return new Response($content);
    }
}
