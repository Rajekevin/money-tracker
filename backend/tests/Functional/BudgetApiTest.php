<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Application\Budget\Port\AlertNotifier;
use App\Infrastructure\Notification\RecordingAlertNotifier;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Goes through HTTP, routing and the container, with the in-memory adapters of the test environment.
 */
final class BudgetApiTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = self::createClient();
        // Keep the same container between requests, so the in-memory repository keeps its data.
        $this->client->disableReboot();
    }

    #[Test]
    public function it_tracks_a_budget_from_definition_to_overspending(): void
    {
        $this->json('PUT', '/api/budgets/2026-10', ['limit' => '500']);
        self::assertResponseStatusCodeSame(201);

        $this->json('POST', '/api/budgets/2026-10/expenses', ['amount' => '520', 'label' => 'Réparation voiture', 'spentOn' => '2026-10-12']);
        self::assertResponseStatusCodeSame(201);

        $this->json('GET', '/api/budgets/2026-10');
        self::assertResponseIsSuccessful();
        self::assertSame([
            'month' => '2026-10',
            'limit' => '500.00',
            'spent' => '520.00',
            'remaining' => '-20.00',
            'status' => 'exceeded',
            'expenseCount' => 1,
        ], $this->responseData());

        $notifier = self::getContainer()->get(AlertNotifier::class);
        self::assertInstanceOf(RecordingAlertNotifier::class, $notifier);
        self::assertCount(1, $notifier->overspendingAlerts);
    }

    #[Test]
    public function an_unknown_budget_is_a_404(): void
    {
        $this->json('GET', '/api/budgets/2031-01');

        self::assertResponseStatusCodeSame(404);
        self::assertSame(['error' => 'No budget is defined for 2031-01.'], $this->responseData());
    }

    #[Test]
    public function a_business_rule_violation_is_a_422(): void
    {
        $this->json('PUT', '/api/budgets/2026-10', ['limit' => '500']);
        $this->json('POST', '/api/budgets/2026-10/expenses', ['amount' => '40', 'label' => 'Cinéma', 'spentOn' => '2026-11-03']);

        self::assertResponseStatusCodeSame(422);
    }

    #[Test]
    public function a_missing_field_is_a_400(): void
    {
        $this->json('PUT', '/api/budgets/2026-10', ['plafond' => '500']);

        self::assertResponseStatusCodeSame(400);
    }

    #[Test]
    public function defining_the_same_month_twice_is_a_409(): void
    {
        $this->json('PUT', '/api/budgets/2026-10', ['limit' => '500']);
        $this->json('PUT', '/api/budgets/2026-10', ['limit' => '800']);

        self::assertResponseStatusCodeSame(409);
    }

    /**
     * @param array<string, string> $body
     */
    private function json(string $method, string $uri, array $body = []): void
    {
        $this->client->request(
            $method,
            $uri,
            server: ['CONTENT_TYPE' => 'application/json'],
            content: [] === $body ? null : json_encode($body, \JSON_THROW_ON_ERROR),
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function responseData(): array
    {
        /** @var array<string, mixed> $data */
        $data = json_decode((string) $this->client->getResponse()->getContent(), true, 512, \JSON_THROW_ON_ERROR);

        return $data;
    }
}
