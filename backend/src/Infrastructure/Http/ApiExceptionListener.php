<?php

declare(strict_types=1);

namespace App\Infrastructure\Http;

use App\Application\Budget\Exception\BudgetAlreadyDefined;
use App\Application\Budget\Exception\BudgetNotFound;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

/**
 * Maps business errors to HTTP answers for the /api routes, as JSON.
 */
#[AsEventListener]
final class ApiExceptionListener
{
    public function __invoke(ExceptionEvent $event): void
    {
        if (!str_starts_with($event->getRequest()->getPathInfo(), '/api/')) {
            return;
        }

        $error = $event->getThrowable();

        $status = match (true) {
            $error instanceof \DomainException => Response::HTTP_UNPROCESSABLE_ENTITY,
            $error instanceof BudgetNotFound => Response::HTTP_NOT_FOUND,
            $error instanceof BudgetAlreadyDefined => Response::HTTP_CONFLICT,
            $error instanceof HttpExceptionInterface => $error->getStatusCode(),
            default => null,
        };

        if (null === $status) {
            return; // Unexpected errors keep Symfony's default handling and logging.
        }

        $event->setResponse(new JsonResponse(['error' => $error->getMessage()], $status));
    }
}
