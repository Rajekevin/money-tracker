<?php

declare(strict_types=1);

namespace App\Infrastructure\Http;

use App\Application\Budget\DefineMonthlyBudget\DefineMonthlyBudget;
use App\Application\Budget\DefineMonthlyBudget\DefineMonthlyBudgetHandler;
use App\Application\Budget\GetBudgetSummary\GetBudgetSummary;
use App\Application\Budget\GetBudgetSummary\GetBudgetSummaryHandler;
use App\Application\Budget\RecordExpense\RecordExpense;
use App\Application\Budget\RecordExpense\RecordExpenseHandler;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Driving adapter: turns HTTP requests into application commands and queries.
 * Errors are mapped to HTTP codes by ApiExceptionListener.
 */
#[Route('/budgets/{month}', requirements: ['month' => '\d{4}-\d{2}'])]
final readonly class BudgetController
{
    public function __construct(
        private DefineMonthlyBudgetHandler $defineMonthlyBudget,
        private RecordExpenseHandler $recordExpense,
        private GetBudgetSummaryHandler $getBudgetSummary,
    ) {
    }

    #[Route('', name: 'budget_define', methods: ['PUT'])]
    public function define(string $month, Request $request): JsonResponse
    {
        $payload = JsonPayload::from($request);

        ($this->defineMonthlyBudget)(new DefineMonthlyBudget($month, $payload->string('limit')));

        return $this->summary($month, Response::HTTP_CREATED);
    }

    #[Route('/expenses', name: 'budget_record_expense', methods: ['POST'])]
    public function recordExpense(string $month, Request $request): JsonResponse
    {
        $payload = JsonPayload::from($request);

        ($this->recordExpense)(new RecordExpense(
            $month,
            $payload->string('amount'),
            $payload->string('label'),
            $payload->string('spentOn'),
        ));

        return $this->summary($month, Response::HTTP_CREATED);
    }

    #[Route('', name: 'budget_summary', methods: ['GET'])]
    public function show(string $month): JsonResponse
    {
        return $this->summary($month, Response::HTTP_OK);
    }

    private function summary(string $month, int $status): JsonResponse
    {
        return new JsonResponse(($this->getBudgetSummary)(new GetBudgetSummary($month)), $status);
    }
}
