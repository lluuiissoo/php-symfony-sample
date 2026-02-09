# Quickstart: Social Profile & Follow System

**Date**: 2026-02-08
**Feature Branch**: `001-social-profile`

## Prerequisites

- PHP 8.2+
- Composer 2.x
- SQLite extension enabled (`pdo_sqlite`)

## Setup

```bash
# Clone and enter project
git clone <repo-url>
cd php-symfony-sample
git checkout 001-social-profile

# Install dependencies
composer install

# Run migrations (creates SQLite database automatically)
php bin/console doctrine:migrations:migrate --no-interaction

# Create upload directory for profile photos
mkdir -p public/uploads/photos

# Start development server
symfony server:start
# or: php -S localhost:8000 -t public/
```

## Verify

1. Open `http://localhost:8000/register` — signup form loads
2. Create an account with email and password
3. Complete the profile wizard
4. Open `http://localhost:8000/users` — user directory loads

## Run Tests

```bash
# All tests
php bin/phpunit

# Specific test suites
php bin/phpunit tests/Controller/
php bin/phpunit tests/Repository/
php bin/phpunit tests/Entity/
```

## Key Configuration

- Database: `DATABASE_URL` in `.env` (SQLite by default)
- Upload directory: `config/packages/vich_uploader.yaml`
- Security: `config/packages/security.yaml`

## Common Tasks

```bash
# Create a migration after entity changes
php bin/console make:migration

# Clear cache
php bin/console cache:clear

# Run code style check
vendor/bin/php-cs-fixer fix --dry-run

# Run static analysis
vendor/bin/phpstan analyse src/
```
