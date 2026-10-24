<?php

declare(strict_types=1);

namespace App\Domain\Budget;

enum BudgetStatus: string
{
    /** Less than the warning threshold is spent. */
    case OnTrack = 'on_track';

    /** The warning threshold is reached, the limit is not. */
    case Warning = 'warning';

    /** More than the limit is spent. */
    case Exceeded = 'exceeded';
}
