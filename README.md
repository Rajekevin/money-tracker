# 💸 Money Tracker

**Money Tracker** is a modern fullstack application to manage and track personal expenses and income.  
It is built with **Symfony (API)** and **Vue.js (SPA)**, designed with **Clean Architecture**, and following **DDD**, **TDD**, and **BDD** best practices.  
Everything runs in **Docker**, ready for development and deployment.

## 📐 Architecture

```
money-tracker/
├── back_myexpensetracker/       # Symfony 6.x (PHP 8.3)
│   ├── config/                  # Configuration files
│   ├── src/                     # Domain (DDD), Application, Interfaces
│   └── tests/                   # PHPUnit + Behat + PhpSpec tests
│
├── front_myexpensetracker/      # Vue.js 3 (Vite)
│   ├── src/                     # Components, views, stores
│   └── dist/                    # Built SPA output
│
├── docker/
│   ├── php/                     # Dockerfile for PHP-FPM
│   └── nginx/                   # Nginx config
│
├── docker-compose.yaml
└── README.md
```

## 🚀 Getting Started

### 1. ✅ Clone the project

```bash
git clone git@github.com:your-username/money-tracker.git
cd money-tracker
```

### 2. 🐳 Launch Docker stack

```bash
docker compose up --build
```

This starts:

| Container         | Description                        | Port          |
|------------------|------------------------------------|---------------|
| `db`             | PostgreSQL 16                      | `5432`        |
| `pgadmin`        | DB Admin UI                        | `http://localhost:5050` |
| `php-backend`    | PHP 8.3 + Symfony backend          | Internally exposed |
| `nginx-server`   | Serves Vue frontend + Symfony API | `http://localhost:8080` |
| `vue-frontend`   | Vue.js build runner (exits after build) | - |

### 3. ⚙️ Install Symfony dependencies

```bash
docker compose exec php composer install
```

### 4. 🧪 Create and migrate the database

```bash
docker compose exec php php bin/console doctrine:database:create
docker compose exec php php bin/console doctrine:migrations:migrate
```

### 5. 🎨 Build the frontend Vue.js app

```bash
docker compose run --rm frontend npm install
docker compose run --rm frontend npm run build
```

## 🌐 Access the application

| URL                             | Description               |
|----------------------------------|---------------------------|
| `http://localhost:8080/`         | Vue.js frontend SPA       |
| `http://localhost:8080/default`  | Symfony backend test route |
| `http://localhost:8080/api/...`  | Symfony API (secured or public) |
| `http://localhost:5050/`         | pgAdmin UI (admin@admin.com / admin) |

## 🧪 Run Tests

### Backend

#### ✅ PHPUnit

```bash
docker compose exec php ./vendor/bin/phpunit
```

#### ✅ Behat (BDD)

```bash
docker compose exec php ./vendor/bin/behat
```

#### ✅ PhpSpec (optional for DDD)

```bash
docker compose exec php ./vendor/bin/phpspec run
```

## 🧱 Technologies

### Backend

- PHP 8.3
- Symfony 6.x
- Doctrine ORM + Migrations
- PHPUnit, Behat, PhpSpec
- DDD (Domain Driven Design)
- TDD / BDD

### Frontend

- Vue.js 3 (Composition API)
- Vite.js
- Pinia (store)
- TypeScript (optional)
- Axios / Fetch API

### Infrastructure

- PostgreSQL 16
- Docker & Docker Compose
- Nginx + PHP-FPM
- pgAdmin
- Compatible with CI/CD (GitHub Actions, Jenkins, GitLab CI)

## 🧑‍💻 Developer Utilities

| Tool            | Access                                      |
|------------------|---------------------------------------------|
| Symfony Profiler | `http://localhost:8080/_profiler`          |
| PgAdmin          | `http://localhost:5050` login: `admin/admin` |
| Xdebug (optional)| Configure in `php/dockerfile` if needed    |
| Faker            | Data generation in development             |

## 🧼 Clean Architecture Domains (DDD)

- `src/Domain`: Entities, Value Objects, Interfaces
- `src/Application`: Use Cases (services, commands)
- `src/Infrastructure`: Adapters (Doctrine, HTTP)
- `src/Controller`: Symfony HTTP Controllers

Each layer communicates only with the layer below it using interfaces → no tight coupling.

## 👨‍💻 Author

**Rajekevin**  
Senior Fullstack Developer — PHP, Symfony, Java, Angular  
🔗 [LinkedIn](https://www.linkedin.com/in/rajekevin)  
📫 Contact: rajekevin@hotmail.fr

## 📄 License

This project is open-sourced under the MIT license.