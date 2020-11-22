install:
	composer install

run:
	php src/lib/run.php

sanitycheck: lint test

lint: phpcs

phpcs:
	vendor/bin/phpcs --standard=PSR2 src tests

fix:
	vendor/bin/phpcbf --standard=PSR2 src tests

test:
	vendor/bin/phpunit --coverage-html coverage --whitelist src --bootstrap src/lib/init.php  tests/
