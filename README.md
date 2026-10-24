# Money Tracker

Track your monthly spending against a budget, and get warned before you leave the green.

The backend is a Symfony 7.4 application built in **hexagonal architecture** (ports & adapters).
The business rules are written first as **Behat scenarios in French**, then implemented test-first with **PHPUnit**.
**Deptrac** checks in CI that dependencies only point inwards.

## The rules

- A budget covers one calendar month and has a spending limit.
- Every expense must belong to the budget's month.
- At **80 %** of the limit, the budget is *à surveiller* and a warning is sent, once.
- Above the limit, the budget is *dans le rouge* and an overspending alert is sent, once.
- Amounts are stored in cents, never as floats.

They are spelled out in [`backend/features/rester_dans_le_vert.feature`](backend/features/rester_dans_le_vert.feature).

## Architecture

```
backend/src
├── Domain/          Pure PHP. Money, Month, Expense, MonthlyBudget (aggregate), events,
│                    and the BudgetRepository port. No framework code allowed here.
├── Application/     Use cases: DefineMonthlyBudget, RecordExpense, GetBudgetSummary,
│                    and the AlertNotifier port.
└── Infrastructure/  Adapters: HTTP controller, JSON file and in-memory repositories,
                     logger and recording notifiers. Wired in config/services.yaml.
```

Dependencies go `Infrastructure → Application → Domain`, enforced by [`deptrac.yaml`](backend/deptrac.yaml).
Swapping an adapter (a database instead of the JSON file, email instead of logs) is a one-line change in `config/services.yaml`.

## API

| Method | Path | Body | Result |
| --- | --- | --- | --- |
| `PUT` | `/api/budgets/{YYYY-MM}` | `{"limit": "500"}` | 201 with the summary, 409 if it exists |
| `POST` | `/api/budgets/{YYYY-MM}/expenses` | `{"amount": "120", "label": "Courses", "spentOn": "2026-10-05"}` | 201 with the summary, 422 if a rule is broken |
| `GET` | `/api/budgets/{YYYY-MM}` | | 200 with the summary, 404 if unknown |

## Run it

```bash
make up                 # PHP, Nginx, PostgreSQL, front
make backend-install    # composer install
make qa                 # coding standards, PHPStan max, Deptrac, PHPUnit, Behat
```

Without Docker, from `backend/`: `composer install`, then `composer qa`.

## Next slices

- [ ] Doctrine adapter for `BudgetRepository` (PostgreSQL is already in `docker-compose.yaml`), with an integration test sharing the JSON adapter's contract
- [ ] Email adapter for `AlertNotifier` with Symfony Mailer
- [ ] Categories and a per-category limit, scenarios first
- [ ] Carry the remaining amount over to the next month (or not): decide in a Gherkin scenario
- [ ] Reconnect the Vue front (`front_myexpensetracker/`) to the new API
- [ ] Commit `backend/composer.lock` after the first local `composer install`

## License

MIT
