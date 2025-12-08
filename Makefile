build:
	docker compose build

install:
	docker compose run --rm php composer install

update:
	docker compose run --rm php composer update

test:
	docker compose run --rm php ./vendor/bin/phpunit

coverage:
	docker compose run --rm php ./vendor/bin/phpunit --coverage-html coverage --coverage-text

phpstan:
	docker compose run --rm php ./vendor/bin/phpstan analyse --memory-limit=256M

cs-fix:
	docker compose run --rm php ./vendor/bin/php-cs-fixer fix

cs-check:
	docker compose run --rm php ./vendor/bin/php-cs-fixer fix --dry-run --diff

shell:
	docker compose run --rm php sh

clean:
	docker compose down --rmi local -v
	rm -rf coverage/
