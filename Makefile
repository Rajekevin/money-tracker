up:
	docker compose up --build

down:
	docker compose down

restart:
	docker compose down && docker compose up --build

php-bash:
	docker compose exec php sh

backend-install:
	docker compose exec php composer install

frontend-install:
	docker compose run --rm frontend npm install

frontend-build:
	docker compose run --rm frontend npm run build

test:
	docker compose exec php ./vendor/bin/phpunit

behat:
	docker compose exec php ./vendor/bin/behat --strict

logs:
	docker compose logs -f --tail=50

qa:
	docker compose exec php composer qa
