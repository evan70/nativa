.PHONY: install update sync-marko dev test analyse lint check build clean serve frontend-dev frontend-build db-init db-reset db-init-no-seed db-init-cardboard db-init-articles db-init-portfolio help

help:
	@echo "Nativa Dev & Prod Circuits"
	@echo ""
	@echo "Usage: make <target>"
	@echo ""
	@echo "Dev circuit:"
	@echo "  install       Install dependencies from lock file (PHP + frontend)"
	@echo "  update        Update all packages to latest (Marko, PHP, frontend)"
	@echo "  sync-marko    Sync Marko framework packages from upstream"
	@echo "  dev           Start dev server (PHP + frontend HMR)"
	@echo "  serve         Start PHP dev server only (port 9000)"
	@echo "  frontend-dev  Start frontend HMR dev server"
	@echo "  test          Run Pest tests"
	@echo "  analyse       Run PHPStan static analysis"
	@echo "  lint          Run composer validate"
	@echo "  check         Run lint + analyse + test"
	@echo ""
	@echo "Prod circuit:"
	@echo "  build         Build production artifact to dist/"
	@echo "  frontend-build Build frontend assets for production"
	@echo ""
	@echo "Database:"
	@echo "  db-init           Init all databases (drop + recreate + seed)"
	@echo "  db-reset          Alias for db-init"
	@echo "  db-init-no-seed   Schema only, no seed data"
	@echo "  db-init-cardboard Init cardboard.db only"
	@echo "  db-init-articles  Init articles.db only"
	@echo "  db-init-portfolio Init portfolio.db only"
	@echo ""
	@echo "Utility:"
	@echo "  clean         Remove vendor/, dist/, node_modules"
	@echo "  help          Show this help"

# ──────────────────────────────────────────────
# Dev circuit
# ──────────────────────────────────────────────

install:
	composer install
	cd templates && pnpm install

update: sync-marko
	composer update
	cd templates && pnpm update

SYNC_TAG ?= 0.6.0
# Packages to exclude from sync (custom module.php — keep our changes)
SYNC_EXCLUDE := errors-simple errors-advanced
sync-marko:
	@echo "Syncing Marko packages from upstream (tag $(SYNC_TAG))..."
	@if [ -d /tmp/marko-upstream ]; then rm -rf /tmp/marko-upstream; fi
	git clone --depth 1 --branch $(SYNC_TAG) https://github.com/marko-php/marko.git /tmp/marko-upstream 2>/dev/null
	@for pkg in packages/*/; do \
		name=$$(basename $$pkg); \
		if echo "$(SYNC_EXCLUDE)" | grep -qw "$$name"; then \
			echo "   EXCLUDED $$name"; \
		elif [ -d "/tmp/marko-upstream/packages/$$name" ]; then \
			echo "   $$name"; \
			rsync -a --quiet --delete "/tmp/marko-upstream/packages/$$name/" "packages/$$name/"; \
		else \
			echo "   SKIP $$name (not in upstream)"; \
		fi; \
	done
	@rm -rf /tmp/marko-upstream
	@echo "Done."

dev:
	@echo "Starting PHP dev server on port 9000..."
	@echo "Run 'make frontend-dev' in another terminal for HMR"
	php -S localhost:9000 -t public

serve:
	php -S localhost:9000 -t public

frontend-dev:
	cd templates && pnpm dev

frontend-build:
	cd templates && pnpm build

test:
	composer test

analyse:
	composer analyse

lint:
	composer validate

check:
	composer check

# ──────────────────────────────────────────────
# Prod circuit
# ──────────────────────────────────────────────

build: frontend-build
	php build.php


# ──────────────────────────────────────────────
# Database (Dev)
# ──────────────────────────────────────────────

db-init:
	php database/init.php --force

db-reset: db-init

db-init-no-seed:
	php database/init.php --force --no-seed

db-init-cardboard:
	php database/init.php cardboard --force

db-init-articles:
	php database/init.php articles --force

db-init-portfolio:
	php database/init.php portfolio --force

clean:
	rm -rf vendor dist
	rm -rf templates/node_modules
	rm -f composer.lock
	rm -f templates/pnpm-lock.yaml
