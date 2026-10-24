<?php

declare(strict_types=1);

namespace App\Infrastructure\Notification;

use App\Application\Budget\Port\AlertNotifier;
use App\Domain\Budget\Event\BudgetExceeded;
use App\Domain\Budget\Event\BudgetWarningReached;

/**
 * Adapter that keeps alerts in memory, so scenarios and tests can check what was sent.
 */
final class RecordingAlertNotifier implements AlertNotifier
{
    /** @var list<BudgetWarningReached> */
    public array $warnings = [];

    /** @var list<BudgetExceeded> */
    public array $overspendingAlerts = [];

    public function warnApproachingLimit(BudgetWarningReached $event): void
    {
        $this->warnings[] = $event;
    }

    public function alertOverspending(BudgetExceeded $event): void
    {
        $this->overspendingAlerts[] = $event;
    }

    public function count(): int
    {
        return \count($this->warnings) + \count($this->overspendingAlerts);
    }
}
