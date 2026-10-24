<?php

declare(strict_types=1);

namespace App\Application\Budget\Port;

use App\Domain\Budget\Event\BudgetExceeded;
use App\Domain\Budget\Event\BudgetWarningReached;

/**
 * Port: how the user is told about their budget (log, email, push...).
 */
interface AlertNotifier
{
    public function warnApproachingLimit(BudgetWarningReached $event): void;

    public function alertOverspending(BudgetExceeded $event): void;
}
