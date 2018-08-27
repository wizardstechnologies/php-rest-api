PHPUNIT_BIN = ./bin/phpunit
PHPCS_BIN = ./bin/phpcs
PHPSTAN_BIN = ./bin/phpstan
PHPMD_BIN = ./bin/phpmd
.PHONY: test

analyse:
	$(PHPMD_BIN) src text cleancode,codesize,design,naming,unusedcode,controversial
	$(PHPSTAN_BIN) analyse src --level=7
	$(PHPCS_BIN) --config-set installed_paths vendor/escapestudios/symfony2-coding-standard
	$(PHPCS_BIN) --standard=Symfony src
	$(PHPCS_BIN) --standard=Symfony tests

test:
	$(PHPUNIT_BIN)
