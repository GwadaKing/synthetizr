<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpKernel\KernelInterface;

class FrontController
{
    private string $frontPath;

    public function __construct(KernelInterface $kernel)
    {
        // chemin absolu vers ton index.html Svelte
        $this->frontPath = $kernel->getProjectDir() . '/../synthetizrFront/index.html';
    }

    #[Route('/{reactRouting}', name: 'frontend', requirements: ['reactRouting' => '.*'], defaults: ['reactRouting' => ''])]
    public function index(): Response
    {
        if (!file_exists($this->frontPath)) {
            return new Response('Front-end Svelte non trouvé.', 500);
        }

        return new Response(file_get_contents($this->frontPath));
    }
}
