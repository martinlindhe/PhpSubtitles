.PHONY: test

install-composer:
	curl -s https://getcomposer.org/installer | php

install-deps:
update-deps:
	php composer.phar update --dev

test:
	./vendor/bin/phpunit

lint:
	./vendor/bin/phpcs --standard=test/phpcs-ruleset.xml bin class test

