DIR := $(shell dirname $(realpath $(lastword $(MAKEFILE_LIST))))
PROJECT_NAME = siglot
EXEC_SHELL = bash
DOCKER_COMPOSE = docker compose -f $(DIR)/docker/docker-compose.yaml -p $(PROJECT_NAME)

### Docker

build: ## Build the docker containers
	$(DOCKER_COMPOSE) build
.PHONY: build

build-jekyll: ## Build the Jekyll container only (useful to update dependencies)
	$(DOCKER_COMPOSE) build jekyll
.PHONY: build-jekyll

jekyll-clean: ## Clean all Jekyll content
	$(DOCKER_COMPOSE) exec jekyll $(EXEC_SHELL) -c "bundle exec jekyll clean"
	$(DOCKER_COMPOSE) down -v jekyll
	$(DOCKER_COMPOSE) up jekyll -d
.PHONY: jekyll-clean

up: ## Start the docker containers
	$(DOCKER_COMPOSE) up -d
.PHONY: up

down: ## Stop the docker containers
	$(DOCKER_COMPOSE) down -v
.PHONY: down

logs: ## Show the logs of the containers
	$(DOCKER_COMPOSE) logs -f
.PHONY: logs

bash-8.1: ## Start a shell in the PHP 8.1 container
	$(DOCKER_COMPOSE) exec php-8.1 $(EXEC_SHELL)
.PHONY: bash-8.1
bash-8.2: ## Start a shell in the PHP 8.2 container
	$(DOCKER_COMPOSE) exec php-8.2 $(EXEC_SHELL)
.PHONY: bash-8.2
bash-8.3: ## Start a shell in the PHP 8.3 container
	$(DOCKER_COMPOSE) exec php-8.3 $(EXEC_SHELL)
.PHONY: bash-8.3
bash-jekyll: ## Start a shell in the Jekyll container
	$(DOCKER_COMPOSE) exec jekyll $(EXEC_SHELL)
.PHONY: bash-jekyll

### QA

local-ci: ## Run the Composer "local-ci" script in every containers
	$(DOCKER_COMPOSE) exec php-8.1 $(EXEC_SHELL) -c "composer local-ci"
	$(DOCKER_COMPOSE) exec php-8.2 $(EXEC_SHELL) -c "composer local-ci"
	$(DOCKER_COMPOSE) exec php-8.3 $(EXEC_SHELL) -c "composer local-ci"
.PHONY: local-ci

### Misc

help: ## Display this help
	@grep -hE '(^[a-zA-Z_0-9.-]+:.*?##.*$$)|(^###)' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}' | sed -e 's/\[32m##/[33m\n/'
.PHONY: help
.DEFAULT_GOAL := help
