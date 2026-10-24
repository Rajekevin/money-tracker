<?php

declare(strict_types=1);

namespace App\Infrastructure\Notification;

use App\Application\Budget\Port\AlertNotifier;
use App\Domain\Budget\Event\BudgetExceeded;
use App\Domain\Budget\Event\BudgetWarningReached;
use Psr\Log\LoggerInterface;

/**
 * First adapter: writes alerts to the "budget" log channel.
 * An email or push adapter can replace it without touching the application layer.
 */
final readonly class LoggerAlertNotifier implements AlertNotifier
{
    public function __construct(private LoggerInterface $budgetLogger)
    {
    }

    public function warnApproachingLimit(BudgetWarningReached $event): void
    {
        $this->budgetLogger->warning('Budget {month}: {spent} EUR spent out of {limit} EUR.', [
            'month' => $event->month->toString(),
            'spent' => $event->spent->toDecimalString(),
            'limit' => $event->limit->toDecimalString(),
        ]);
    }

    public function alertOverspending(BudgetExceeded $event): void
    {
        $this->budgetLogger->error('Budget {month} exceeded by {overspent} EUR (limit {limit} EUR).', [
            'month' => $event->month->toString(),
            'overspent' => $event->overspent->toDecimalString(),
            'limit' => $event->limit->toDecimalString(),
        ]);
    }
}
