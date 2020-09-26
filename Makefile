.PHONY: clean code-style coverage help static-analysis update-dependencies install-dependencies infection-testing
.DEFAULT_GOAL := coverage

PHPUNIT =  ./vendor/bin/phpunit -c ./phpunit.xml
PHPSTAN  = ./vendor/bin/phpstan
PHPCS = ./vendor/bin/phpcs --extensions=php
PHPCBF = ./vendor/bin/
INFECTION = ./vendor/bin/infection
CONSOLE = ./bin/console
COVCHECK = ./vendor/bin/coverage-check

clean:
	rm -rf ./build ./vendor

code-style:
	${PHPCS} --report-full --report-gitblame --standard=PSR12 ./src

coverage:
	${PHPUNIT} && ${COVCHECK} ./build/logs/phpunit/clover.xml 100
	cp ./build/logs/phpunit/clover.xml ./

fix-code-style:
	${PHPCBF} src/ --standard=PSR12

infection-testing:
	make coverage
	${INFECTION} --coverage=build/logs/phpunit --min-msi=68 --threads=`nproc`

static-analysis:
	${PHPSTAN} analyse --no-progress

update-dependencies:
	composer update

install-dependencies:
	composer install

help:
	# Usage:
	#   make <target> [OPTION=value]
	#
	# Targets:
	#   clean                Cleans the coverage and the vendor directory
	#   code-style           Check codestyle using phpcs
	#   coverage (default)   Generate code coverage (html, clover)
	#   fix-code-style       Fix code style
	#   help                 You're looking at it!
	#   infection-testing    Run infection/mutation testing
	#   static-analysis      Run static analysis using phpstan
	#   update-dependencies  Run composer update
	#   install-dependencies Run composer install
