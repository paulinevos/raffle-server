test:
	make test-cs
	make test-unit

test-unit:
	vendor/bin/phpunit

test-cs:
	vendor/bin/phpcs --standard=PSR12 src/ tests/

fix-cs:
	vendor/bin/phpcbf --standard=PSR12 src/ tests/