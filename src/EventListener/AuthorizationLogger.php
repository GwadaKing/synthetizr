<?php

namespace App\EventListener;

use Symfony\Component\HttpKernel\Event\RequestEvent;
use Psr\Log\LoggerInterface;

class AuthorizationLogger
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        $authHeader = $request->headers->get('Authorization');

        // Log la valeur brute du header
        $this->logger->info('Authorization header reçu : ' . var_export($authHeader, true));
    }
}
