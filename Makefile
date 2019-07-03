PHPUNIT_BIN = ./bin/phpunit
PHPCS_BIN = ./bin/phpcs
PHPSTAN_BIN = ./bin/phpstan
PHPMD_BIN = ./bin/phpmd
.PHONY: test

analysis:
	php -l src
	php -l tests
	$(PHPSTAN_BIN) analyse src --level=7
	$(PHPCS_BIN) --standard=PSR2 src
	$(PHPCS_BIN) --standard=PSR2 tests
	$(PHPMD_BIN) src text cleancode,codesize,controversial,design,naming,unusedcode

test:
	$(PHPUNIT_BIN)
