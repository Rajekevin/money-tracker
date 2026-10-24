<?php

declare(strict_types=1);

namespace App\Behat;

use App\Application\Budget\DefineMonthlyBudget\DefineMonthlyBudget;
use App\Application\Budget\DefineMonthlyBudget\DefineMonthlyBudgetHandler;
use App\Application\Budget\GetBudgetSummary\BudgetSummary;
use App\Application\Budget\GetBudgetSummary\GetBudgetSummary;
use App\Application\Budget\GetBudgetSummary\GetBudgetSummaryHandler;
use App\Application\Budget\RecordExpense\RecordExpense;
use App\Application\Budget\RecordExpense\RecordExpenseHandler;
use App\Domain\Budget\BudgetStatus;
use App\Domain\Budget\Exception\InvalidExpense;
use App\Domain\Budget\Money;
use App\Infrastructure\Notification\RecordingAlertNotifier;
use App\Infrastructure\Persistence\InMemoryBudgetRepository;
use Behat\Behat\Context\Context;
use Behat\Step\Given;
use Behat\Step\Then;
use Behat\Step\When;
use PHPUnit\Framework\Assert;

/**
 * Drives the application layer directly, with in-memory adapters:
 * the scenarios describe business rules, not HTTP.
 */
final class BudgetContext implements Context
{
    private const array MONTHS = [
        'janvier' => 1, 'février' => 2, 'mars' => 3, 'avril' => 4, 'mai' => 5, 'juin' => 6,
        'juillet' => 7, 'août' => 8, 'septembre' => 9, 'octobre' => 10, 'novembre' => 11, 'décembre' => 12,
    ];

    private const array STATUSES = [
        'dans le vert' => BudgetStatus::OnTrack,
        'à surveiller' => BudgetStatus::Warning,
        'dans le rouge' => BudgetStatus::Exceeded,
    ];

    private InMemoryBudgetRepository $budgets;

    private RecordingAlertNotifier $notifier;

    private string $month = '';

    private ?\Throwable $lastError = null;

    private int $alertsBeforeAction = 0;

    public function __construct()
    {
        $this->budgets = new InMemoryBudgetRepository();
        $this->notifier = new RecordingAlertNotifier();
    }

    #[Given('/^un budget de (\d+(?:,\d{1,2})?) € pour (\p{L}+ \d{4})$/u')]
    public function unBudgetDe(string $amount, string $month): void
    {
        $this->month = $this->toMonth($month);
        (new DefineMonthlyBudgetHandler($this->budgets))(new DefineMonthlyBudget($this->month, $amount));
    }

    #[Given('/^(?:que )?j\'ai déjà dépensé (\d+(?:,\d{1,2})?) € pour "([^"]+)" le (\d{1,2} \p{L}+ \d{4})$/u')]
    public function jaiDejaDepense(string $amount, string $label, string $date): void
    {
        $this->recordExpense($amount, $label, $date);
        Assert::assertNull($this->lastError, 'The setup expense should have been accepted.');
    }

    #[When('/^je dépense (\d+(?:,\d{1,2})?) € pour "([^"]+)" le (\d{1,2} \p{L}+ \d{4})$/u')]
    public function jeDepense(string $amount, string $label, string $date): void
    {
        $this->alertsBeforeAction = $this->notifier->count();
        $this->recordExpense($amount, $label, $date);
    }

    #[Then('/^il me reste (\d+(?:,\d{1,2})?) €$/u')]
    public function ilMeReste(string $amount): void
    {
        Assert::assertSame(Money::fromEuros($amount)->toDecimalString(), $this->summary()->remaining);
    }

    #[Then('/^je suis à découvert de (\d+(?:,\d{1,2})?) €$/u')]
    public function jeSuisADecouvertDe(string $amount): void
    {
        Assert::assertSame(Money::fromEuros('-'.$amount)->toDecimalString(), $this->summary()->remaining);
    }

    #[Then('/^mon budget est "([^"]+)"$/u')]
    public function monBudgetEst(string $status): void
    {
        Assert::assertArrayHasKey($status, self::STATUSES, sprintf('Unknown status "%s".', $status));
        Assert::assertSame(self::STATUSES[$status]->value, $this->summary()->status);
    }

    #[Then('je n\'ai reçu aucune alerte')]
    public function aucuneAlerte(): void
    {
        Assert::assertSame(0, $this->notifier->count());
    }

    #[Then('je n\'ai reçu aucune nouvelle alerte')]
    public function aucuneNouvelleAlerte(): void
    {
        Assert::assertSame($this->alertsBeforeAction, $this->notifier->count());
    }

    #[Then('j\'ai reçu un avertissement')]
    public function unAvertissement(): void
    {
        Assert::assertCount(1, $this->notifier->warnings);
        Assert::assertCount(0, $this->notifier->overspendingAlerts);
    }

    #[Then('/^j\'ai reçu une alerte de dépassement de (\d+(?:,\d{1,2})?) €$/u')]
    public function uneAlerteDeDepassement(string $amount): void
    {
        Assert::assertCount(1, $this->notifier->overspendingAlerts);
        Assert::assertTrue(Money::fromEuros($amount)->equals($this->notifier->overspendingAlerts[0]->overspent));
    }

    #[Then('/^la dépense est refusée car elle n\'appartient pas à (\p{L}+ \d{4})$/u')]
    public function laDepenseEstRefusee(string $month): void
    {
        Assert::assertInstanceOf(InvalidExpense::class, $this->lastError);
        Assert::assertStringContainsString($this->toMonth($month), $this->lastError->getMessage());
    }

    private function recordExpense(string $amount, string $label, string $date): void
    {
        $this->lastError = null;

        try {
            (new RecordExpenseHandler($this->budgets, $this->notifier))(
                new RecordExpense($this->month, $amount, $label, $this->toDate($date)),
            );
        } catch (\DomainException $error) {
            $this->lastError = $error;
        }
    }

    private function summary(): BudgetSummary
    {
        return (new GetBudgetSummaryHandler($this->budgets))(new GetBudgetSummary($this->month));
    }

    /** "octobre 2026" → "2026-10" */
    private function toMonth(string $frenchMonth): string
    {
        [$name, $year] = explode(' ', $frenchMonth);

        return sprintf('%s-%02d', $year, self::MONTHS[mb_strtolower($name)] ?? throw new \InvalidArgumentException($name));
    }

    /** "5 octobre 2026" → "2026-10-05" */
    private function toDate(string $frenchDate): string
    {
        [$day, $name, $year] = explode(' ', $frenchDate);

        return sprintf('%s-%02d', $this->toMonth($name.' '.$year), (int) $day);
    }
}
