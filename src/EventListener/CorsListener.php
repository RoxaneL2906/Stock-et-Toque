<?php

namespace App\EventListener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpFoundation\Response;

class CorsListener
{
    public function __construct(
        private readonly string $originAutorisee,
    ) {
    }

    /**
     * Répond immédiatement aux requêtes de "préflight" (OPTIONS) envoyées
     * automatiquement par le navigateur avant certaines requêtes (POST avec JSON, etc.).
     */
    #[AsEventListener(event: KernelEvents::REQUEST, priority: 250)]
    public function onKernelRequest(RequestEvent $event): void
    {
        if ($event->getRequest()->getMethod() === 'OPTIONS') {
            $response = new Response();
            $this->ajouterHeadersCors($response);
            $event->setResponse($response);
        }
    }

    /**
     * Ajoute les headers CORS à chaque réponse réelle de l'API.
     */
    #[AsEventListener(event: KernelEvents::RESPONSE)]
    public function onKernelResponse(ResponseEvent $event): void
    {
        $this->ajouterHeadersCors($event->getResponse());
    }

    private function ajouterHeadersCors(Response $response): void
    {
        $response->headers->set('Access-Control-Allow-Origin', $this->originAutorisee);
        $response->headers->set('Access-Control-Allow-Credentials', 'true');
        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization');
    }
}