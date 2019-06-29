PROJECT_PATH = .
DC = docker-compose
DC_RUN = $(DC) run --rm
COMPOSER = $(DC_RUN) composer

vendor: composer.lock
	@$(COMPOSER) install

clean: ${PROJECT_PATH}
	@rm -rf ${PROJECT_PATH}/vendor && \
	$(DC) down -v --remove-orphans

install: clean vendor ## Clean, pull containers, run composer install and npm install
	notify-send "Make install completed"
